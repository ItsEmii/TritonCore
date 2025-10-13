<?php

namespace hcf\handler\kit\command\subcommand;

use hcf\HCFLoader;  
use hcf\handler\kit\Kit;
use hcf\handler\kit\command\KitSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class GiveSubCommand implements KitSubCommand {

    public function execute(CommandSender $sender, array $args) : void {
    
        if (count($args) < 3) {
            $sender->sendMessage(TextFormat::RED."Uso: /kit give [category: free|pay|legendary] [target: player] [string: kit]");
            return;
        }
        $category = $args[0];
        $targetName = $args[1];
        $kitName = $args[2];

        if ($category === "free") {
            $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);

            if (!$kit) {
                $sender->sendMessage(TextFormat::RED."Kit $kitName no encontrado!");
                return;
            }

            if (!$targetPlayer instanceof Player) {
                $sender->sendMessage(TextFormat::RED."Jugador $targetName no encontrado!");
                return;
            }

            $kit->giveTo($targetPlayer);

            $targetPlayer->sendMessage(TextFormat::GREEN."Se te ha dado el kit {$kit->getName()}");
            $sender->sendMessage(TextFormat::GREEN."Le has dado el kit {$kit->getName()} a {$targetPlayer->getName()}");
        } elseif ($category === "pay") {
            $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($kitName);

            if (!$kit) {
                $sender->sendMessage(TextFormat::RED."Kit $kitName no encontrado!");
                return;
            }

            if (!$targetPlayer instanceof Player) {
                $sender->sendMessage(TextFormat::RED."Jugador $targetName no encontrado!");
                return;
            }

            $kit->giveTo($targetPlayer);

            $targetPlayer->sendMessage(TextFormat::GREEN."Se te ha dado el kit {$kit->getName()}");
            $sender->sendMessage(TextFormat::GREEN."Le has dado el kit {$kit->getName()} a {$targetPlayer->getName()}");
            
        } elseif ($category === "legendary") {
            $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($kitName);

            if (!$kit) {
                $sender->sendMessage(TextFormat::RED."Kit $kitName no encontrado!");
                return;
            }

            if (!$targetPlayer instanceof Player) {
                $sender->sendMessage(TextFormat::RED."Jugador $targetName no encontrado!");
                return;
            }

            $kit->giveTo($targetPlayer);

            $targetPlayer->sendMessage(TextFormat::GREEN."Se te ha dado el kit {$kit->getName()}");
            $sender->sendMessage(TextFormat::GREEN."Le has dado el kit {$kit->getName()} a {$targetPlayer->getName()}");
        
        }
    }
}