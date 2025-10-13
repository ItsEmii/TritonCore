<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DepositSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction'));
            return;
        }
        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f deposit [cantidad | all]'));
            return;
        }
        $cantidad = $args[0];

        if ($cantidad < 0) {
            return;
        }

        if ($cantidad === "all") {
            $faction->setBalance($faction->getBalance() + $sender->getSession()->getBalance());
            $sender->sendMessage('§aEl nuevo balance de la faction es ' . $faction->getBalance() . '$');
            $sender->getSession()->setBalance(0);
            return;
        }

        if (!is_numeric($cantidad)) {
            $sender->sendMessage('§cUsa /f deposit [cantidad | all]');
            return;
        }

        if ($sender->getSession()->getBalance() >= $cantidad) {
            $faction->setBalance($faction->getBalance() + (int)$cantidad);
            $sender->sendMessage('§aEl nuevo balance de la faction es ' . $faction->getBalance() . '$');
            $sender->getSession()->setBalance($sender->getSession()->getBalance() - (int)$cantidad);
        } else {
            $sender->sendMessage('§cLa cantidad que ingresaste excede tu balance!');
        }
    }
}