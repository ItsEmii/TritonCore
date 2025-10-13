<?php

declare(strict_types=1);

namespace hcf\module\coinshop\utils;

use hcf\HCFLoader;
use hcf\player\Player;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\console\ConsoleCommandSender;

final class Utils
{

    public static function createBasicNBT(Player $player): CompoundTag
    {
        $nbt = CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($player->getLocation()->x),
                new DoubleTag($player->getLocation()->y),
                new DoubleTag($player->getLocation()->z)
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($player->getMotion()->x),
                new DoubleTag($player->getMotion()->y),
                new DoubleTag($player->getMotion()->z)
            ]))
            ->setTag("Rotation", new ListTag([
                new FloatTag($player->getLocation()->yaw),
                new FloatTag($player->getLocation()->pitch)
            ]));
        return $nbt;
    }

    public static function openCoinShop(Player $player): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->getInventory()->setContents([
            0 => self::getItem(241, 1),
            1 => self::getItem(241, 1),
            7 => self::getItem(241, 1),
            8 => self::getItem(241, 1),
            9 => self::getItem(241, 1),
            17 => self::getItem(241, 1),
            36 => self::getItem(241, 1),
            44 => self::getItem(241, 1),
            45 => self::getItem(241, 1),
            46 => self::getItem(241, 1),
            52 => self::getItem(241, 1),
            53 => self::getItem(241, 1)
        ]);
        
        $menu->getInventory()->setItem(12, self::getItem(131)->setCustomName(TextFormat::colorize('Comprar Llaves')));
        $menu->getInventory()->setItem(14, self::getItem(241)->setCustomName(TextFormat::colorize('Comprar Rangos Temporales')));
        
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
            $type = TextFormat::clean($item->getCustomName());
            
            if (self::getPage($type) !== null) {
                self::openPageCoinShop($player, $type);
                $player->removeCurrentWindow();
            }
            return $transaction->discard();
        });
        $menu->send($player, TextFormat::colorize('&7Coins Shop, tus coins son: &e$'. $player->getSession()->getCrystals()));
    }

    public static function openPageCoinShop(Player $player, string $type): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->getInventory()->setContents([
            0 => self::getItem(241, 1),
            1 => self::getItem(241, 1),
            7 => self::getItem(241, 1),
            8 => self::getItem(241, 1),
            9 => self::getItem(241, 1),
            17 => self::getItem(241, 1),
            36 => self::getItem(241, 1),
            44 => self::getItem(241, 1),
            45 => self::getItem(241, 1),
            46 => self::getItem(241, 1),
            52 => self::getItem(241, 1),
            53 => self::getItem(241, 1)
        ] + self::getPage($type));
        
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
            
            if ($item->getNamedTag()->getTag('price') !== null) {
                $newItem = $item->setLore([]);
                $newBalance = $player->getSession()->getCrystals() - $item->getNamedTag()->getInt('price');

                if ($newBalance < 0) {
                    $player->sendMessage(TextFormat::colorize('&c¡No tienes suficientes coins para comprar este objeto!'));
                    return $transaction->discard();
                }

                $itemName = TextFormat::clean($item->getCustomName());
                $playerName = $player->getName();
                $command = "";
                $message = "";

                if (str_ends_with($itemName, ' Key')) {
                    $keyName = str_replace(' Key', '', $itemName);
                    $command = "key give $keyName 1 \"$playerName\"";
                    $message = "&aHas comprado &l&c$itemName! &r&aTu nuevo saldo es &e$$newBalance";
                }
                
                if (str_ends_with($itemName, ' Rank 5d')) {
                    $rankName = str_replace(' Rank 5d', '', $itemName);
                    $command = "ranks set $playerName $rankName 5d";
                    $message = "&aHas comprado &l&c$rankName &7Rango por 5d! &r&aTu nuevo saldo es &e$$newBalance";
                }

                if (!empty($command)) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command);
                    $player->getSession()->setCrystals($newBalance);
                    $player->sendMessage(TextFormat::colorize($message));
                } else if ($player->getInventory()->canAddItem($newItem)) {
                    $player->getInventory()->addItem($newItem);
                    $player->getSession()->setCrystals($newBalance);
                } else {
                    $player->sendMessage(TextFormat::colorize('&c¡Este objeto no puede guardarse en tu inventario!'));
                }
            }
            return $transaction->discard();
        });
        $menu->setInventoryCloseListener(function (Player $player, $inventory) {
            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->isOnline())
                    self::openCoinShop($player);
            }), 1);
        });
        $menu->send($player, TextFormat::colorize('&7' . $type));
    }
    
    private static function prepareItem(Item $item, int $price): Item
    {
        $item->setLore([TextFormat::colorize('&fPrecio: &6$' . $price)]);
        
        $namedtag = $item->getNamedTag();
        $namedtag->setInt('price', $price);
        $item->setNamedTag($namedtag);
        
        return $item;
    }
    
    private static function getPage(string $type): ?array
    {
        switch ($type) {
            case 'Comprar Llaves':
                return [
                    11 => self::prepareItem(self::getItem(131, 1, 1), 100)->setCustomName(TextFormat::colorize('&l&4Llave KoTH')),
                    12 => self::prepareItem(self::getItem(131, 7, 1), 50)->setCustomName(TextFormat::colorize('&l&cLlave Evil')),
                    13 => self::prepareItem(self::getItem(131, 14, 1), 50)->setCustomName(TextFormat::colorize('&l&bLlave Angel')),
                    14 => self::prepareItem(self::getItem(131, 11, 1), 50)->setCustomName(TextFormat::colorize('&l&aLlave Blessed')),
                    15 => self::prepareItem(self::getItem(131, 5, 1), 50)->setCustomName(TextFormat::colorize('&l&bLlave Fall')),
                    16 => self::prepareItem(self::getItem(131, 10, 1), 50)->setCustomName(TextFormat::colorize('&l&6Llave Rare')),
                    17 => self::prepareItem(self::getItem(131, 12, 1), 50)->setCustomName(TextFormat::colorize('&l&eLlave Items')),
                    18 => self::prepareItem(self::getItem(131, 0, 1), 50)->setCustomName(TextFormat::colorize('&l&9Llave Starter'))
                ];
                
            case 'Comprar Rangos Temporales':
                return [
                    11 => self::prepareItem(self::getItem(241, 14, 1), 500)->setCustomName(TextFormat::colorize('&l&cRango Legend &7por 5d')),
                    12 => self::prepareItem(self::getItem(241, 9, 1), 300)->setCustomName(TextFormat::colorize('&l&bRango Angel &7por 5d')),
                    13 => self::prepareItem(self::getItem(241, 5, 1), 300)->setCustomName(TextFormat::colorize('&l&aRango Blessed &7por 5d')),
                    14 => self::prepareItem(self::getItem(241, 4, 1), 300)->setCustomName(TextFormat::colorize('&l&eRango Faith &7por 5d')),
                    15 => self::prepareItem(self::getItem(241, 13, 1), 300)->setCustomName(TextFormat::colorize('&l&2Rango Peaceful &7por 5d')),
                    16 => self::prepareItem(self::getItem(241, 10, 1), 300)->setCustomName(TextFormat::colorize('&l&dRango Believer &7por 5d')),
                    17 => self::prepareItem(self::getItem(241, 6, 1), 300)->setCustomName(TextFormat::colorize('&l&5Rango Decoy &7por 5d'))
                ];
        }
        return null;
    }

    public static function getItem($id, $meta = 0, $count = 1): Item {
        return LegacyStringToItemParser::getInstance()->parse("{$id}:{$meta}")->setCount($count);
    }
}