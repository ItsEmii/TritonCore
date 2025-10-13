<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PingCommand extends Command
{

    public function __construct()
    {
        parent::__construct('ping', HCFLoader::$prefix . '§hMuestra tu ping o el de otro jugador');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (count($args) === 0) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize("&7Tu ping: &a" . $sender->getNetworkSession()->getPing() . "ms"));
            return;
        }

        $target = HCFLoader::getInstance()->getServer()->getPlayerExact($args[0]);
        if ($target !== null) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize("&7Ping de &f" . $target->getName() . "&7: &a" . $target->getNetworkSession()->getPing() . "ms"));
        } else {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize("&cEse jugador no está conectado"));
        }
    }
}