<?php

namespace hcf\module\blockshop\utils;

use hcf\player\Player;
use hcf\utils\Utils;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\PotionTypeRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

final class ShopAndSell {

    /**
     * Youtube Zenji (zDarxsEz)
     */

    public static function Shop(Player $player): void {

        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName("§7Shop");
    
        $menu->getInventory()->setContents([
            11 => VanillaItems::EMERALD()->setCustomName("§4Sell Shop"),
            13 => VanillaItems::POTION()->setCustomName("§ePotions Shop"),
            15 => VanillaBlocks::POPPY()->asItem()->setCustomName("§aBuy Shop"),
        ]);

        $menu->setListener(function(InvMenuTransaction $transaction) use ($menu) : InvMenuTransactionResult{
            /** @var Player $player */
            $player = $transaction->getPlayer();

            //Shell Shop
            IF (!$player instanceof Player) return $transaction->discard();

            //goback1
            if($transaction->getItemClicked()->getCustomName() === "§cGo Back§r"){
                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    11 => VanillaItems::EMERALD()->setCustomName("§4Sell Shop"),
                    13 => VanillaItems::POTION()->setCustomName("§ePotions Shop"),
                    15 => VanillaBlocks::POPPY()->asItem()->setCustomName("§aBuy Shop"),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§4Sell Shop"){
                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    3 => VanillaBlocks::GOLD()->asItem()->setCount(16)->setCustomName("§eGold Block\n§ePrice: §d1250"),
                    4 => VanillaBlocks::DIAMOND()->asItem()->setCount(16)->setCustomName("§eDiamond Block\n§ePrice: §d1800"),
                    5 => VanillaBlocks::EMERALD()->asItem()->setCount(16)->setCustomName("§eEmerald Block\n§ePrice: §d2000"),
                    12 => VanillaBlocks::REDSTONE()->asItem()->setCount(16)->setCustomName("§eRedstone Block\n§ePrice: §d600"),
                    13 => VanillaBlocks::LAPIS_LAZULI()->asItem()->setCount(16)->setCustomName("§eLapis Block\n§ePrice: §d1800"),
                    14 => VanillaBlocks::IRON()->asItem()->setCount(16)->setCustomName("§eIron Block\n§ePrice: §d800"),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back§r")->setLore(["§r§7Return page"]),
                    22 => VanillaBlocks::COAL()->asItem()->setCount(16)->setCustomName("§eCoal Block\n§ePrice: §d300")
                ]);

                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§eGold Block\n§ePrice: §d1250") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::GOLD()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 1250);
                    $player->sendMessage("§aYour purchase successfully + 1800 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eDiamond Block\n§ePrice: §d1800") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::DIAMOND()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 1800);
                    $player->sendMessage("§aYour purchase successfully + 1800 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eEmerald Block\n§ePrice: §d2000") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::EMERALD()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 2000);
                    $player->sendMessage("§aYour purchase successfully + 2000 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eRedstone Block\n§ePrice: §d600") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::REDSTONE()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 600);
                    $player->sendMessage("§aYour purchase successfully + 600 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eLapis Block\n§ePrice: §d1800") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::LAPIS_LAZULI()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 1800);
                    $player->sendMessage("§aYour purchase successfully + 1800 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eIron Block\n§ePrice: §d800") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::IRON()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 800);
                    $player->sendMessage("§aYour purchase successfully + 800 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eCoal Block\n§ePrice: §d300") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::COAL()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 300);
                    $player->sendMessage("§aYour purchase successfully + 300 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            if($transaction->getItemClicked()->getCustomName() === "§eCoal Block\n§ePrice: §d300") {
                $item = null;

                foreach($player->getInventory()->getContents() as $playerInventory)
                if ($playerInventory->equals(VanillaBlocks::COAL()->asItem()))

                $item = $playerInventory;

                if ($item === null) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }

                if ($item->getCount() >= 16) {
                    $player->getInventory()->removeItem($item->setCount(16));
                    $player->getSession()->setBalance($player->getSession()->getBalance() + 300);
                    $player->sendMessage("§aYour purchase successfully + 300 balance");
                    Utils::PlaySound($player, "random.orb", 1, 1);
                }

                if ($item->getCount() < 16) {
                    $player->sendMessage("§cYou don't have enough block");
                    Utils::PlaySound($player, "random.pop", 1, 1);
                }
            }

            //Potion Shop
            if($transaction->getItemClicked()->getCustomName() === "§ePotions Shop"){
                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    11 => self::prepareItem(self::getItem(373, 8, 1)->setCustomName('§ePotion'), 1000),
                    13 => self::prepareItem(self::getItem(373, 13, 1)->setCustomName('§ePotion'), 800),
                    14 => self::prepareItem(self::getItem(438, 25, 1)->setCustomName('§ePotion'), 1200),
                    15 => self::prepareItem(self::getItem(438, 17, 1), 1200)->setCustomName("§ePotion"),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back§r")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§aBuy Shop"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    3 => self::getItem(155)->setCustomName('§cNether Blocks'),
                    4 => self::getItem(241, 0)->setCustomName('§cGlass Blocks'),
                    5 => self::getItem(1)->setCustomName('§cStone Blocks'),
                    12 => self::getItem(35)->setCustomName('§7Wool Blocks'),
                    14 => self::getItem(159)->setCustomName('§cClay Blocks'),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back§r")->setLore(["§r§7Return page"]),
                    21 => self::getItem(17)->setCustomName('§aWood Blocks'),
                    22 => self::getItem(7)->setCustomName('§eOther Blocks'),
                    23 => self::getItem(24, 3)->setCustomName('§eDesert Blocks'),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            //goback2
            if($transaction->getItemClicked()->getCustomName() === "§cGo Back"){
                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    3 => self::getItem(155)->setCustomName('§cNether Blocks'),
                    4 => self::getItem(241, 0)->setCustomName('§cGlass Blocks'),
                    5 => self::getItem(1)->setCustomName('§cStone Blocks'),
                    12 => self::getItem(35)->setCustomName('§7Wool Blocks'),
                    14 => self::getItem(159)->setCustomName('§cClay Blocks'),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back§r")->setLore(["§r§7Return page"]),
                    21 => self::getItem(17)->setCustomName('§aWood Blocks'),
                    22 => self::getItem(7)->setCustomName('§eOther Blocks'),
                    23 => self::getItem(24, 3)->setCustomName('§eDesert Blocks'),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§cNether Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    3 => self::prepareItem(self::getItem(88, 0, 64), 800),
                    4 => self::prepareItem(self::getItem(87, 0, 64), 64),
                    5 => self::prepareItem(self::getItem(112, 0, 64), 64),
                    11 => self::prepareItem(self::getItem(153, 0, 64), 64),
                    15 => self::prepareItem(self::getItem(49, 0, 64), 500),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                    21 => self::prepareItem(self::getItem(155, 0, 64), 64),
                    22 => self::prepareItem(self::getItem(155, 2, 64), 64),
                    23 => self::prepareItem(self::getItem(155, 1, 64), 64),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§cGlass Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    0 => self::prepareItem(self::getItem(241, 0, 64), 64),
                    1 => self::prepareItem(self::getItem(241, 1, 64), 64),
                    2 => self::prepareItem(self::getItem(241, 2, 64), 64),
                    3 => self::prepareItem(self::getItem(241, 3, 64), 64),
                    4 => self::prepareItem(self::getItem(241, 4, 64), 64),
                    5 => self::prepareItem(self::getItem(241, 5, 64), 64),
                    6 => self::prepareItem(self::getItem(241, 6, 64), 64),
                    7 => self::prepareItem(self::getItem(241, 7, 64), 64),
                    8 => self::prepareItem(self::getItem(241, 8, 64), 64),
                    9 => self::prepareItem(self::getItem(241, 9, 64), 64),
                    10 => self::prepareItem(self::getItem(241, 10, 64), 64),
                    11 => self::prepareItem(self::getItem(241, 11, 64), 64),
                    12 => self::prepareItem(self::getItem(241, 12, 64), 64),
                    13 => self::prepareItem(self::getItem(241, 13, 64), 64),
                    14 => self::prepareItem(self::getItem(241, 14, 64), 64),
                    15 => self::prepareItem(self::getItem(241, 15, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§cStone Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    4 => self::prepareItem(self::getItem(97, 2, 64), 64),
                    11 => self::prepareItem(self::getItem(139, 0, 64), 64),
                    12 => self::prepareItem(self::getItem(108, 0, 64), 64),
                    14 => self::prepareItem(self::getItem(44, 0, 64), 64),
                    15 => self::prepareItem(self::getItem(139, 8, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                    22 => self::prepareItem(self::getItem(1, 0, 64), 64),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§7Wool Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    0 => self::prepareItem(self::getItem(35, 0, 64), 64),
                    1 => self::prepareItem(self::getItem(35, 1, 64), 64),
                    2 => self::prepareItem(self::getItem(35, 2, 64), 64),
                    3 => self::prepareItem(self::getItem(35, 3, 64), 64),
                    4 => self::prepareItem(self::getItem(35, 4, 64), 64),
                    5 => self::prepareItem(self::getItem(35, 5, 64), 64),
                    6 => self::prepareItem(self::getItem(35, 6, 64), 64),
                    7 => self::prepareItem(self::getItem(35, 7, 64), 64),
                    8 => self::prepareItem(self::getItem(35, 8, 64), 64),
                    9 => self::prepareItem(self::getItem(35, 9, 64), 64),
                    10 => self::prepareItem(self::getItem(35, 10, 64), 64),
                    11 => self::prepareItem(self::getItem(35, 11, 64), 64),
                    12 => self::prepareItem(self::getItem(35, 12, 64), 64),
                    13 => self::prepareItem(self::getItem(35, 13, 64), 64),
                    14 => self::prepareItem(self::getItem(35, 14, 64), 64),
                    15 => self::prepareItem(self::getItem(35, 15, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§cClay Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    0 => self::prepareItem(self::getItem(159, 0, 64), 64),
                    1 => self::prepareItem(self::getItem(159, 1, 64), 64),
                    2 => self::prepareItem(self::getItem(159, 2, 64), 64),
                    3 => self::prepareItem(self::getItem(159, 3, 64), 64),
                    4 => self::prepareItem(self::getItem(159, 4, 64), 64),
                    5 => self::prepareItem(self::getItem(159, 5, 64), 64),
                    6 => self::prepareItem(self::getItem(159, 6, 64), 64),
                    7 => self::prepareItem(self::getItem(159, 7, 64), 64),
                    8 => self::prepareItem(self::getItem(159, 8, 64), 64),
                    9 => self::prepareItem(self::getItem(159, 9, 64), 64),
                    10 => self::prepareItem(self::getItem(159, 10, 64), 64),
                    11 => self::prepareItem(self::getItem(159, 11, 64), 64),
                    12 => self::prepareItem(self::getItem(159, 12, 64), 64),
                    13 => self::prepareItem(self::getItem(159, 13, 64), 64),
                    14 => self::prepareItem(self::getItem(159, 14, 64), 64),
                    15 => self::prepareItem(self::getItem(159, 15, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§aWood Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    11 => self::prepareItem(self::getItem(17, 0, 64), 800),
                    12 => self::prepareItem(self::getItem(17, 1, 64), 800),
                    13 => self::prepareItem(self::getItem(17, 2, 64), 800),
                    14 => self::prepareItem(self::getItem(162, 0, 64), 800),
                    15 => self::prepareItem(self::getItem(162, 1, 64), 800),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§eOther Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    11 => self::prepareItem(self::getItem(121, 0, 64), 64),
                    12 => self::prepareItem(self::getItem(80, 0, 64), 64),
                    14 => self::prepareItem(self::getItem(18, 0, 64), 64),
                    15 => self::prepareItem(self::getItem(18, 1, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            if($transaction->getItemClicked()->getCustomName() === "§eDesert Blocks"){

                $menu->getInventory()->clearAll();
                $menu->getInventory()->setContents([
                    11 => self::prepareItem(self::getItem(24, 0, 64), 64),
                    12 => self::prepareItem(self::getItem(24, 3, 64), 64),
                    13 => self::prepareItem(self::getItem(44, 1, 64), 64),
                    14 => self::prepareItem(self::getItem(128, 0, 64), 64),
                    15 => self::prepareItem(self::getItem(24, 1, 64), 64),
                    18 => VanillaItems::PAPER()->setCustomName("§cGo Back")->setLore(["§r§7Return page"]),
                ]);
                Utils::PlaySound($player, "random.click", 1, 1);
            }

            $items = $transaction->getItemClicked();
            
            if($items->getNamedTag()->getTag('price') !== null){
                $newItem = $items->setLore([]);
                $newBalance = $player->getSession()->getBalance() - $items->getNamedTag()->getInt('price');
                if ($newBalance < 0) {
                    $player->sendMessage(TextFormat::colorize("&cYou don't have enough money"));
                    Utils::PlaySound($player, "random.pop", 1, 1);
                    return $transaction->discard();
                }
                if ($player->getInventory()->canAddItem($newItem)) {
                    $player->getInventory()->addItem($newItem);
                    $player->getSession()->setBalance($newBalance);
                    $player->sendMessage(TextFormat::colorize('&aYour purchase successfully'));
                    Utils::PlaySound($player, "random.orb", 1, 1);
                } else {
                    $player->sendMessage(TextFormat::colorize('&cYour inventory is full'));
                    Utils::PlaySound($player, "random.pop", 1, 1);

                }
            }
            return $transaction->discard();
        });
        $menu->send($player);

    }

    public static function getItem($id, $meta = 0, $count = 1): Item {
        return LegacyStringToItemParser::getInstance()->parse("{$id}:{$meta}")->setCount($count);
    }

    private static function prepareItem(Item $item, int $price): Item
    {
        $item->setLore([TextFormat::colorize('&ePrice: &d$' . $price)]);
        
        $namedtag = $item->getNamedTag();
        $namedtag->setInt('price', $price);
        $item->setNamedTag($namedtag);
        
        return $item;
    }
    //By Zenji (10/11/2023) Pete puto el que empieze a decir que la creo
}