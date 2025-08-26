<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\Calendar\Main;
use JonasWindmann\Calendar\event\CalendarEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EditSubCommand extends SubCommand {
    private Main $plugin;

    /**
     * EditSubCommand constructor
     * 
     * @param Main $plugin The plugin instance
     */
    public function __construct(Main $plugin) {
        parent::__construct(
            "edit",
            "Edit server events",
            "/calendar edit",
            0,
            0,
            "calendar.edit"
        );

        $this->plugin = $plugin;
    }

    /**
     * Execute the subcommand
     * 
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void {
        try {
            $player = $this->senderToPlayer($sender);
        } catch (\InvalidArgumentException $e) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return;
        }

        // Check permission
        if (!$player->hasPermission("calendar.edit")) {
            $player->sendMessage(TextFormat::RED . "You don't have permission to edit events.");
            return;
        }

        $this->showEventEditor($player);
    }

    /**
     * Show the event editor to a player
     * 
     * @param Player $player
     */
    private function showEventEditor(Player $player): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::GOLD . "Event Editor",
            TextFormat::GRAY . "Select an action:",
            function (Player $player, ?int $buttonIndex) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }

                switch ($buttonIndex) {
                    case 0: // Create new event
                        $this->showCreateEventForm($player);
                        break;
                    case 1: // Edit existing event
                        $this->showEventList($player, true);
                        break;
                    case 2: // Delete event
                        $this->showEventList($player, false, true);
                        break;
                }
            }
        );

        $form->button(TextFormat::GREEN . "Create New Event");
        $form->button(TextFormat::YELLOW . "Edit Existing Event");
        $form->button(TextFormat::RED . "Delete Event");
        $form->sendTo($player);
    }

    /**
     * Show the create event form to a player
     * 
     * @param Player $player
     */
    private function showCreateEventForm(Player $player): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createCustomForm(
            TextFormat::GREEN . "Create New Event",
            function (Player $player, ?array $data) {
                if ($data === null) {
                    return; // Form was closed
                }

                // Validate input
                if (empty($data["title"])) {
                    $player->sendMessage(TextFormat::RED . "Event title cannot be empty.");
                    return;
                }

                // Parse date
                $timestamp = strtotime($data["date"]);
                if ($timestamp === false) {
                    $player->sendMessage(TextFormat::RED . "Invalid date format. Please use YYYY-MM-DD HH:MM.");
                    return;
                }

                // Create new event
                $title = $data["title"];
                $description = $data["description"];
                $location = $data["location"];
                $isActive = $data["active"];

                $this->plugin->getEventManager()->createEvent($title, $description, $timestamp, $location, $isActive);
                $player->sendMessage(TextFormat::GREEN . "Event created successfully!");

                // Return to the event editor
                $this->showEventEditor($player);
            }
        );

        $form->input("Title", "Enter event title", "", "title");
        $form->input("Description", "Enter event description", "", "description");
        $form->input("Date (YYYY-MM-DD HH:MM)", date("Y-m-d H:i", time() + 3600), "", "date"); // Default to 1 hour from now
        $form->input("Location", "Enter event location", "", "location");
        $form->toggle("Active", true, "active");
        $form->sendTo($player);
    }

    /**
     * Show the event list to a player for editing or deleting
     * 
     * @param Player $player
     * @param bool $forEditing Whether the list is for editing events
     * @param bool $forDeleting Whether the list is for deleting events
     */
    private function showEventList(Player $player, bool $forEditing = false, bool $forDeleting = false): void {
        $eventManager = $this->plugin->getEventManager();
        $events = $eventManager->getEvents();

        if (empty($events)) {
            $player->sendMessage(TextFormat::YELLOW . "There are no events to " . ($forEditing ? "edit" : "delete") . ".");
            return;
        }

        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::GOLD . ($forEditing ? "Edit Event" : "Delete Event"),
            TextFormat::GRAY . "Select an event to " . ($forEditing ? "edit" : "delete") . ":",
            function (Player $player, ?int $buttonIndex) use ($events, $forEditing, $forDeleting) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }

                if ($buttonIndex === count($events)) {
                    // Back button
                    $this->showEventEditor($player);
                    return;
                }

                // Get the event at the selected index
                $event = array_values($events)[$buttonIndex];

                if ($forEditing) {
                    $this->showEditEventForm($player, $event);
                } else if ($forDeleting) {
                    $this->showDeleteEventConfirmation($player, $event);
                }
            }
        );

        // Add a button for each event
        foreach ($events as $event) {
            $date = date("Y-m-d H:i", $event->getTimestamp());
            $form->button(TextFormat::YELLOW . $event->getTitle() . "\n" . TextFormat::GRAY . $date);
        }

        // Add a back button
        $form->button(TextFormat::RED . "Back");

        $form->sendTo($player);
    }

    /**
     * Show the edit event form to a player
     * 
     * @param Player $player
     * @param CalendarEvent $event
     */
    private function showEditEventForm(Player $player, CalendarEvent $event): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createCustomForm(
            TextFormat::YELLOW . "Edit Event",
            function (Player $player, ?array $data) use ($event) {
                if ($data === null) {
                    return; // Form was closed
                }

                // Validate input
                if (empty($data["title"])) {
                    $player->sendMessage(TextFormat::RED . "Event title cannot be empty.");
                    return;
                }

                // Parse date
                $timestamp = strtotime($data["date"]);
                if ($timestamp === false) {
                    $player->sendMessage(TextFormat::RED . "Invalid date format. Please use YYYY-MM-DD HH:MM.");
                    return;
                }

                // Update event
                $title = $data["title"];
                $description = $data["description"];
                $location = $data["location"];
                $isActive = $data["active"];

                $this->plugin->getEventManager()->updateEvent(
                    $event->getId(),
                    $title,
                    $description,
                    $timestamp,
                    $location,
                    $isActive
                );

                $player->sendMessage(TextFormat::GREEN . "Event updated successfully!");

                // Return to the event editor
                $this->showEventEditor($player);
            }
        );

        $form->input("Title", "Enter event title", $event->getTitle(), "title");
        $form->input("Description", "Enter event description", $event->getDescription(), "description");
        $form->input("Date (YYYY-MM-DD HH:MM)", "YYYY-MM-DD HH:MM", date("Y-m-d H:i", $event->getTimestamp()), "date");
        $form->input("Location", "Enter event location", $event->getLocation(), "location");
        $form->toggle("Active", $event->isActive(), "active");
        $form->sendTo($player);
    }

    /**
     * Show the delete event confirmation to a player
     * 
     * @param Player $player
     * @param CalendarEvent $event
     */
    private function showDeleteEventConfirmation(Player $player, CalendarEvent $event): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::RED . "Delete Event",
            TextFormat::GRAY . "Are you sure you want to delete the event \"" . $event->getTitle() . "\"?",
            function (Player $player, ?int $buttonIndex) use ($event) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }

                if ($buttonIndex === 0) {
                    // Delete the event
                    $this->plugin->getEventManager()->deleteEvent($event->getId());
                    $player->sendMessage(TextFormat::GREEN . "Event deleted successfully!");
                }

                // Return to the event editor
                $this->showEventEditor($player);
            }
        );

        $form->button(TextFormat::RED . "Delete");
        $form->button(TextFormat::GREEN . "Cancel");
        $form->sendTo($player);
    }
}
