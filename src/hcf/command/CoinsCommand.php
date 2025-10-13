<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class CoinsCommand extends Command
{
    public function __construct()
    {
        parent::__construct('coins', '§hUsa este comando para ver tus coins');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede usarlo un jugador.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }
        
        $coins = $sender->getSession()->getCrystals();
        $sender->sendMessage($prefix . "Tus coins: " . TextFormat::GREEN . "$" . $coins);
    }
}