<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar\command;

use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\Calendar\command\subcommand\EditSubCommand;
use JonasWindmann\Calendar\command\subcommand\SettingsSubCommand;
use JonasWindmann\Calendar\command\subcommand\ViewSubCommand;
use JonasWindmann\Calendar\Main;

class CalendarCommand extends BaseCommand {
    private Main $plugin;

    /**
     * CalendarCommand constructor
     * 
     * @param Main $plugin The plugin instance
     */
    public function __construct(Main $plugin) {
        parent::__construct(
            "calendar",
            "View and manage server events",
            "/calendar [view|edit|settings]",
            ["cal"],
            "calendar.command"
        );

        $this->plugin = $plugin;

        // Register subcommands
        $this->registerSubCommands([
            new ViewSubCommand($plugin),
            new EditSubCommand($plugin),
            new SettingsSubCommand($plugin)
        ]);
    }
}
