<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\Calendar\command\CalendarCommand;
use JonasWindmann\Calendar\manager\EventManager;
use JonasWindmann\Calendar\event\CalendarEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\ClosureTask;
use pocketmine\form\Form;

class Main extends PluginBase implements Listener {
    private EventManager $eventManager;

    protected function onEnable(): void {
        // Create directories and config
        $this->saveDefaultConfig();

        // Set default config values if they don't exist
        $this->saveResource("config.yml", false);

        // Initialize event manager
        $this->eventManager = new EventManager($this);

        // Register command
        $command = new CalendarCommand($this);
        CoreAPI::getInstance()->getCommandManager()->registerCommand($command);

        // Register event listener
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getLogger()->info(TextFormat::GREEN . "Calendar plugin enabled!");
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        // Check if show_on_join is enabled
        if ($this->getConfig()->get("show_on_join", false)) {
            $player = $event->getPlayer();
            $currentEvent = $this->eventManager->getCurrentEvent();

            if ($currentEvent !== null) {
                // Show current event notification
                $this->showEventNotification($player, $currentEvent);
            }
        }
    }

    /**
     * Show an event notification to a player
     * 
     * @param Player $player The player to show the notification to
     * @param CalendarEvent $event The event to show
     */
    private function showEventNotification(Player $player, CalendarEvent $event): void {
        $formManager = CoreAPI::getInstance()->getFormManager();

        // Create a SimpleForm instead of a ModalForm
        $form = $formManager->createSimpleForm(
            TextFormat::GOLD . "Upcoming Event",
            TextFormat::GREEN . "Title: " . TextFormat::WHITE . $event->getTitle() . "\n" .
            TextFormat::GREEN . "Date: " . TextFormat::WHITE . date("Y-m-d H:i", $event->getTimestamp()) . "\n" .
            TextFormat::GREEN . "Location: " . TextFormat::WHITE . $event->getLocation() . "\n\n" .
            TextFormat::GREEN . "Description: " . TextFormat::WHITE . $event->getDescription() . "\n\n",
            function (Player $player, ?int $buttonIndex) {
                // This callback will be called when the form is closed
                // No action needed as this is just a notification
            }
        );

        // Send the form to the player
        $form->sendTo($player);

        // Schedule a task to close the form after 5 seconds
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player): void {
            // Send an empty form that will replace the notification
            $player->sendForm(new class() implements Form {
                public function handleResponse(Player $player, $data): void {}
                public function jsonSerialize(): array {
                    return ["type" => "form", "title" => "", "content" => "", "buttons" => []];
                }
            });
        }), 100); // 5 seconds (100 ticks)
    }

    public function getEventManager(): EventManager {
        return $this->eventManager;
    }
}
