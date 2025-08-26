<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\Calendar\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SettingsSubCommand extends SubCommand {
    private Main $plugin;
    
    /**
     * SettingsSubCommand constructor
     * 
     * @param Main $plugin The plugin instance
     */
    public function __construct(Main $plugin) {
        parent::__construct(
            "settings",
            "Configure calendar settings",
            "/calendar settings",
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
            $player->sendMessage(TextFormat::RED . "You don't have permission to edit calendar settings.");
            return;
        }
        
        $this->showSettingsForm($player);
    }
    
    /**
     * Show the settings form to a player
     * 
     * @param Player $player
     */
    private function showSettingsForm(Player $player): void {
        $formManager = CoreAPI::getInstance()->getFormManager();
        $form = $formManager->createCustomForm(
            TextFormat::GOLD . "Calendar Settings",
            function (Player $player, ?array $data) {
                if ($data === null) {
                    return; // Form was closed
                }
                
                // Update settings
                $config = $this->plugin->getConfig();
                $config->set("show_on_join", $data["show_on_join"]);
                $config->set("max_events_display", (int) $data["max_events_display"]);
                $config->save();
                
                $player->sendMessage(TextFormat::GREEN . "Calendar settings updated successfully!");
            }
        );
        
        $config = $this->plugin->getConfig();
        $showOnJoin = $config->get("show_on_join", false);
        $maxEventsDisplay = $config->get("max_events_display", 10);
        
        $form->toggle("Show Event on Join", $showOnJoin, "show_on_join");
        $form->input("Max Events to Display", "Enter a number", (string) $maxEventsDisplay, "max_events_display");
        $form->sendTo($player);
    }
}