<?php

declare(strict_types=1);

namespace hcf\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ListCommand extends Command
{
    public function __construct()
    {
        parent::__construct('players', '§hUse command for list players');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender->hasPermission("use.player.command")) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        $onlineCount = count($sender->getServer()->getOnlinePlayers());
        $sender->sendMessage(TextFormat::GREEN . "Players playing: §a" . $onlineCount);
    }
}