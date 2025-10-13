<?php

namespace hcf\module\pkg\util;

use hcf\HCFLoader;
use hcf\utils\serialize\Serialize;
use hcf\module\pkg\command\PackageCommand;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\inventory\Inventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class Content {
    
    use SingletonTrait;

    private array $items = [];

    public function __construct() {
        $this->load(); 
    }

    public function getItems(): array {
        return $this->items;
    }
    
    public function getPackage(int $count = 1): Item {
        $ppackageItem = VanillaBlocks::ENDER_CHEST()->asItem();
        $ppackageItem->setCustomName(TextFormat::colorize('§l§dPartner Package§r'));
        $ppackageItem->setCount($count);
        
        $lore = [
            '§dstore.legendsmc.fun',
        ];
        $ppackageItem->setLore(array_map(fn(string $lore) => TextFormat::colorize($lore), $lore));
        $ppackageItem->getNamedTag()->setInt('ppackage', 1);
        
        return $ppackageItem;
    }

    public function load(): void {
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('PartnerPackages', new PackageCommand());
        $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'others' . DIRECTORY_SEPARATOR . 'content.json', Config::JSON);
        $this->items = array_map(fn($data) => Serialize::deserialize($data), $config->getAll());
    }

    public function save(): void {
        $config = new Config(HCFLoader::getInstance()->getDataFolder() . 'others' . DIRECTORY_SEPARATOR . 'content.json', Config::JSON);
        $config->setAll(array_map(fn(Item $item) => Serialize::serialize($item), $this->items));
        $config->save();
    }

    public function sendMenu(Player $player): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setContents($this->items);
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $this->items = $inventory->getContents();
            $this->save();  
            $player->sendMessage(TextFormat::colorize('&aYou have edited the loot and it has been saved.'));
        });
        $menu->send($player, TextFormat::colorize('&ePartner PartnerPackages Loot'));
    }
}