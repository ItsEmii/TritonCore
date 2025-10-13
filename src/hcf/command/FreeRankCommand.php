<?php

namespace hcf\command;

use hcf\player\Player;
use hcf\utils\time\Timer;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FreeRankCommand extends Command
{
    public function __construct()
    {
        parent::__construct('freerank', '§hComando para obtener rango gratuito');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        if ($sender->getSession() === null) {
            $sender->sendMessage($prefix . TextFormat::RED . "Error con la sesión, intenta nuevamente.");
            return;
        }

        $cooldown = $sender->getSession()->getCooldown("freerank.cooldown");
        if ($cooldown !== null) {
            $timeLeft = Timer::getTimeToString($cooldown->getTime());
            $sender->sendMessage($prefix . TextFormat::RED . "Debes esperar $timeLeft para usar este comando otra vez.");
            return;
        }

        $senderName = $sender->getName();

        Server::getInstance()->dispatchCommand(
            new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), 
            'ranks set "' . $senderName . '" Decoy 2d'
        );

        $sender->getSession()->addCooldown('freerank.cooldown', '', 604800, false, false);
        $sender->sendMessage($prefix . TextFormat::GREEN . "Has obtenido el rango Decoy por 2 días.");
    }
}