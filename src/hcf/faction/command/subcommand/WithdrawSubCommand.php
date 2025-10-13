<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class WithdrawSubCommand implements FactionSubCommand
{

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes faction'));
            return;
        }

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f withdraw [cantidad | all]'));
            return;
        }

        $cantidad = strtolower($args[0]);

        if ($cantidad === "all") {
            $balance = $faction->getBalance();
            if ($balance <= 0) {
                $sender->sendMessage(TextFormat::colorize('&cTu faction no tiene balance para retirar.'));
                return;
            }
            $sender->getSession()->setBalance($sender->getSession()->getBalance() + $balance);
            $faction->setBalance(0);
            $sender->sendMessage(TextFormat::colorize('&aHas retirado todo el balance de la faction: $' . $balance));
            return;
        }

        if (!is_numeric($cantidad)) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f withdraw [cantidad | all]'));
            return;
        }

        $cantidad = (int)$cantidad;

        if ($cantidad <= 0) {
            $sender->sendMessage(TextFormat::colorize('&cLa cantidad debe ser positiva.'));
            return;
        }

        if ($faction->getBalance() < $cantidad) {
            $sender->sendMessage(TextFormat::colorize('&cLa cantidad que ingresaste excede el balance de la faction!'));
            return;
        }

        $sender->getSession()->setBalance($sender->getSession()->getBalance() + $cantidad);
        $faction->setBalance($faction->getBalance() - $cantidad);
        $sender->sendMessage(TextFormat::colorize('&aHas retirado $' . $cantidad . '. Tu nuevo balance es $' . $sender->getSession()->getBalance()));
    }
}