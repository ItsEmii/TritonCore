<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FeedCommand extends Command
{
    public function __construct()
    {
        parent::__construct('feed', '§hUsa este comando para alimentarte o alimentar a otro jugador');
        $this->setPermission('feed.command');
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

        if (!isset($args[0])) {
        
            $sender->getHungerManager()->setFood($sender->getHungerManager()->getMaxFood());
            $sender->sendMessage($prefix . TextFormat::GREEN . "Te has alimentado.");
            return;
        }

        if (!$sender->hasPermission('player.feed.command')) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para alimentar a otro jugador.");
            return;
        }

        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$player instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Jugador no encontrado.");
            return;
        }

        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->sendMessage($prefix . TextFormat::GREEN . "Has sido alimentado por " . $sender->getName() . ".");
        $sender->sendMessage($prefix . TextFormat::GREEN . "Has alimentado a " . $player->getName() . ".");
    }
}