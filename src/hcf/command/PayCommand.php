<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PayCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            'pay',
            HCFLoader::$prefix . 'Usa /pay [jugador] [cantidad]'
        );
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&cUsa /pay [jugador] [cantidad]'));
            return;
        }

        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$player instanceof Player) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&cJugador no encontrado'));
            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&cEscribe una cantidad válida en números'));
            return;
        }

        $cantidad = intval($args[1]);

        if ($cantidad <= 0) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&cLa cantidad debe ser mayor que 0'));
            return;
        }

        if ($sender->getSession()->getBalance() < $cantidad) {
            $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&cNo tienes suficiente dinero'));
            return;
        }

        $sender->getSession()->setBalance($sender->getSession()->getBalance() - $cantidad);
        $player->getSession()->setBalance($player->getSession()->getBalance() + $cantidad);

        $sender->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&aLe enviaste $' . $cantidad . ' a ' . $player->getName()));
        $player->sendMessage(HCFLoader::$prefix . TextFormat::colorize('&aRecibiste $' . $cantidad . ' de ' . $sender->getName()));
    }
}