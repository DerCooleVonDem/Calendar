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

class ViewSubCommand extends SubCommand {
    private Main $plugin;
    
    /**
     * ViewSubCommand constructor
     * 
     * @param Main $plugin The plugin instance
     */
    public function __construct(Main $plugin) {
        parent::__construct(
            "view",
            "View upcoming server events",
            "/calendar view",
            0,
            0,
            "calendar.command"
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
        
        $this->showEventList($player);
    }
    
    /**
     * Show the event list to a player
     * 
     * @param Player $player
     */
    private function showEventList(Player $player): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::GREEN . "Server Events",
            TextFormat::GRAY . "Select an option to view events:",
            function (Player $player, ?int $buttonIndex) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }
                
                if ($buttonIndex === 0) {
                    // Show upcoming events
                    $this->showUpcomingEvents($player);
                } else if ($buttonIndex === 1) {
                    // Show past events
                    $this->showPastEvents($player);
                }
            }
        );
        
        $form->button(TextFormat::GREEN . "Upcoming Events");
        $form->button(TextFormat::YELLOW . "Past Events");
        $form->sendTo($player);
    }
    
    /**
     * Show upcoming events to a player
     * 
     * @param Player $player
     */
    private function showUpcomingEvents(Player $player): void {
        $eventManager = $this->plugin->getEventManager();
        $events = $eventManager->getUpcomingEvents();
        
        if (empty($events)) {
            $player->sendMessage(TextFormat::YELLOW . "There are no upcoming events.");
            return;
        }
        
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::GREEN . "Upcoming Events",
            TextFormat::GRAY . "Select an event to view details:",
            function (Player $player, ?int $buttonIndex) use ($events) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }
                
                if ($buttonIndex === count($events)) {
                    // Back button
                    $this->showEventList($player);
                    return;
                }
                
                // Get the event at the selected index
                $event = array_values($events)[$buttonIndex];
                $this->showEventDetails($player, $event);
            }
        );
        
        // Add a button for each event
        foreach ($events as $event) {
            $date = date("Y-m-d H:i", $event->getTimestamp());
            $form->button(TextFormat::GREEN . $event->getTitle() . "\n" . TextFormat::GRAY . $date);
        }
        
        // Add a back button
        $form->button(TextFormat::RED . "Back");
        
        $form->sendTo($player);
    }
    
    /**
     * Show past events to a player
     * 
     * @param Player $player
     */
    private function showPastEvents(Player $player): void {
        $eventManager = $this->plugin->getEventManager();
        $events = $eventManager->getPastEvents();
        
        if (empty($events)) {
            $player->sendMessage(TextFormat::YELLOW . "There are no past events.");
            return;
        }
        
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::YELLOW . "Past Events",
            TextFormat::GRAY . "Select an event to view details:",
            function (Player $player, ?int $buttonIndex) use ($events) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }
                
                if ($buttonIndex === count($events)) {
                    // Back button
                    $this->showEventList($player);
                    return;
                }
                
                // Get the event at the selected index
                $event = array_values($events)[$buttonIndex];
                $this->showEventDetails($player, $event);
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
     * Show event details to a player
     * 
     * @param Player $player
     * @param CalendarEvent $event
     */
    private function showEventDetails(Player $player, CalendarEvent $event): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createSimpleForm(
            TextFormat::GREEN . $event->getTitle(),
            TextFormat::GREEN . "Date: " . TextFormat::WHITE . date("Y-m-d H:i", $event->getTimestamp()) . "\n" .
            TextFormat::GREEN . "Location: " . TextFormat::WHITE . $event->getLocation() . "\n\n" .
            TextFormat::GREEN . "Description: " . TextFormat::WHITE . $event->getDescription(),
            function (Player $player, ?int $buttonIndex) use ($event) {
                if ($buttonIndex === null) {
                    return; // Form was closed
                }
                
                // Back button
                if ($event->isFuture()) {
                    $this->showUpcomingEvents($player);
                } else {
                    $this->showPastEvents($player);
                }
            }
        );
        
        // Add a back button
        $form->button(TextFormat::RED . "Back");
        
        $form->sendTo($player);
    }
}