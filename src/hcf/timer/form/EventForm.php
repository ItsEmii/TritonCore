<?php

namespace hcf\timer\form;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use cosmicpe\form\entries\custom\InputEntry;
use cosmicpe\form\entries\custom\ToggleEntry;
use hcf\entity\TextEntity;
use hcf\player\Player;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class EventForm extends CustomForm
{

    public function __construct() {
        parent::__construct(TextFormat::colorize('&5Event Form'));
        $event = null;
        $boolean = true;
        $this->addEntry(new DropdownEntry(TextFormat::colorize("&7Choose the Event"), ["SoTW", "EoTW", "Purge", "KeyAll", "KeyAllOP", "PackpageAll", ]), function (Player $player, DropdownEntry $entry, int $value) use (&$event): void {
            $event = $value;
        });

        $this->addEntry(new ToggleEntry(TextFormat::colorize("&7Stop or Start (Esta en Start por default)"), true), function (Player $player, ToggleEntry $entry, bool $value) use (&$boolean): void {
            $boolean = $value;
        });

        $this->addEntry(new InputEntry(TextFormat::colorize('&7Time (1h, 2h)'), "", "1h"), function (Player $player, InputEntry $entry, string $value) use (&$time, &$event, &$boolean): void {
            if ($value === '') {
                $player->sendMessage(TextFormat::colorize("&cTiempo Invalido"));
                return;
            }
            $time = $value;
            if ($event === 0) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "sotw start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "sotw stop");
                }
            } elseif ($event === 1) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "eotw start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "eotw stop");
                }
            } elseif ($event === 2) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "purge start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "purge stop");
                }
            } elseif ($event === 3) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "keyall start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "keyall stop");
                }
            } elseif ($event === 4) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "keyallop start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "keyallop stop");
                }
            } elseif ($event === 5) {
                $event = null;
                if ($boolean) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "pkgall start $value");
                } else {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "pkgall stop");
                }
            }
        });
    }
}