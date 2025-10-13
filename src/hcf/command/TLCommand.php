<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class TLCommand extends Command
{
    public function __construct()
    {
        parent::__construct('tl', '§hEnvía tus coordenadas a tu facción');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§bTritonMC§e] ";

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        $faction = $sender->getSession()->getFaction();
        if ($faction === null) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes una facción.");
            return;
        }

        $pos = $sender->getPosition();
        $coords = "[" . (int)$pos->getX() . ", " . (int)$pos->getY() . ", " . (int)$pos->getZ() . "]";

        foreach (Server::getInstance()->getOnlinePlayers() as $online) {
            if (!$online instanceof Player) continue;
            if ($online->getSession()->getFaction() === $faction) {
                $online->sendMessage($prefix . "§c[Team] §f" . $sender->getName() . ": §e" . $coords);
            }
        }
    }
}