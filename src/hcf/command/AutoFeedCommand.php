<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AutoFeedCommand extends Command
{
    public function __construct()
    {
        parent::__construct('autofeed', '§hUsa este comando para activar o desactivar el autofeed');
        $this->setPermission('autofeed.command');
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

        if ($sender->getSession()->hasAutoFeed()) {
            $sender->getSession()->setAutoFeed(false);
            $sender->sendMessage($prefix . TextFormat::RED . "Autofeed desactivado.");
        } else {
            $sender->getSession()->setAutoFeed(true);
            $sender->sendMessage($prefix . TextFormat::GREEN . "Autofeed activado.");
        }
    }
}