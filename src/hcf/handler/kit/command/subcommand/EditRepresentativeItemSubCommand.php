<?php

namespace hcf\handler\kit\command\subcommand;

use hcf\HCFLoader;  
use hcf\handler\kit\Kit;
use hcf\handler\kit\command\KitSubCommand;
use muqsit\invmenu\InvMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;

class EditRepresentativeItemSubCommand implements KitSubCommand {

    public function execute(CommandSender $sender, array $args) : void {
    
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED."Uso: /kit setitem [category: free|pay|legendary] [string: kitname]");
            return;
        }

        $category = $args[0];
        
        if ($category === "free") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitManager();
            $kit = $kitManager->getKit($args[1]);
            if ($kit === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            $item = $sender->getInventory()->getItemInHand();
            $kit->setRepresentativeItem($item);
            $sender->sendMessage(TextFormat::colorize("&aHas cambiado con exito el representative item del gkit"));
        } elseif ($category === "pay") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager();
            $kit = $kitManager->getKit($args[1]);
            if ($kit === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            $item = $sender->getInventory()->getItemInHand();
            $kit->setRepresentativeItem($item);
            $sender->sendMessage(TextFormat::colorize("&aHas cambiado con exito el representative item del gkit"));
        } elseif ($category === "legendary") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitLManager();
            $kit = $kitManager->getKit($args[1]);
            if ($kit === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            $item = $sender->getInventory()->getItemInHand();
            $kit->setRepresentativeItem($item);
            $sender->sendMessage(TextFormat::colorize("&aHas cambiado con exito el representative item del gkit"));
        } else {
            $sender->sendMessage(TextFormat::colorize("&cCategory is not valid use: &efree &cor &epay §cand legendary."));
        }
    }
}