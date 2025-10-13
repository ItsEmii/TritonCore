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

class EditKitSubCommand implements KitSubCommand {

    public function execute(CommandSender $sender, array $args) : void {
    
        if (count($args) < 3) {
            $sender->sendMessage(TextFormat::RED."Uso: /kit editcontent [category: free|pay|legendary] [string: items|armor] [string: kitname]");
            return;
        }
        
        if ($args[0] === "free") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitManager();
            $loots = $kitManager->getKit($args[2]);
            if ($loots === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            if ($args[1] === "items") {
                $chest = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Items"));
                if ($loots->getItems() !== null) {
                    foreach ($loots->getItems() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setItems($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot items has been modified correctly"));
                });
                $chest->send($sender);
                return;
            } elseif ($args[1] === "armor") {
                $chest = InvMenu::create(InvMenu::TYPE_HOPPER);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Armor"));
                if ($loots->getArmor() !== null) {
                    foreach ($loots->getArmor() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setArmor($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot armor has been modified correctly"));
                });
                $chest->send($sender);
                return;
            }
        } elseif ($args[0] === "pay") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager();
            $loots = $kitManager->getKit($args[2]);
            if ($loots === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            if ($args[1] === "items") {
                $chest = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Items"));
                if ($loots->getItems() !== null) {
                    foreach ($loots->getItems() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setItems($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot items has been modified correctly"));
                });
                $chest->send($sender);
                return;
            } elseif ($args[1] === "armor") {
                $chest = InvMenu::create(InvMenu::TYPE_HOPPER);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Armor"));
                if ($loots->getArmor() !== null) {
                    foreach ($loots->getArmor() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setArmor($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot armor has been modified correctly"));
                });
                $chest->send($sender);
                return;
            }
        } elseif ($args[0] === "legendary") {
            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitLManager();
            $loots = $kitManager->getKit($args[2]);
            if ($loots === null) {
                $sender->sendMessage(TextFormat::colorize("&cGkit Invalido"));
                return;
            }
            if ($args[1] === "items") {
                $chest = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Items"));
                if ($loots->getItems() !== null) {
                    foreach ($loots->getItems() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setItems($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot items has been modified correctly"));
                });
                $chest->send($sender);
                return;
            } elseif ($args[1] === "armor") {
                $chest = InvMenu::create(InvMenu::TYPE_HOPPER);
                $chest->setName(TextFormat::colorize("&9GKit &7($args[2]) Armor"));
                if ($loots->getArmor() !== null) {
                    foreach ($loots->getArmor() as $slot => $item) {
                        $chest->getInventory()->setItem($slot, $item);
                    }
                }
                $chest->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($loots): void {
                    $loots->setArmor($inventory->getContents());
                    $player->sendMessage(TextFormat::colorize("&aThe Gkit loot armor has been modified correctly"));
                });
                $chest->send($sender);
                return;
            }
        } else {
            $sender->sendMessage(TextFormat::colorize("&cCategory is not valid use: &efree &cor &epay §cand §elegendary."));
        }
        $sender->sendMessage(TextFormat::RED."Uso: /kit editcontent [items|armor] [string: kitname]");
    }
}