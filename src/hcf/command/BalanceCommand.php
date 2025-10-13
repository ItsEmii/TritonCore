<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BalanceCommand extends Command
{
    public function __construct()
    {
        parent::__construct('balance', '§hUsa este comando para ver tu balance');
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

        $balance = $sender->getSession()->getBalance();
        $sender->sendMessage($prefix . "Tu balance es: " . TextFormat::GREEN . "$" . $balance);
    }
}