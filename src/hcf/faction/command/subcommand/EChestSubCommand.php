<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player as HCFPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\command\CommandSender;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

class EChestSubCommand implements FactionSubCommand
{
    /** @var array<string, Inventory> */
    private static array $factionInventories = [];

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof HCFPlayer)
            return;

        $faction = $sender->getSession()->getFaction();

        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize("&cNo estás en una Faction."));
            return;
        }

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();

        if (!isset(self::$factionInventories[$faction])) {
            self::loadFactionChest($faction, $inventory);
            self::$factionInventories[$faction] = $inventory;
        } else {
            $inventory->setContents(self::$factionInventories[$faction]->getContents());
        }

        $menu->setInventoryCloseListener(function(Player $player, Inventory $inv) use ($faction): void {
            self::$factionInventories[$faction] = $inv;
            self::saveFactionChest($faction, $inv);
        });

        $menu->send($sender, TextFormat::colorize("&6Faction Chest"));
    }

    public static function saveFactionChest(string $faction, Inventory $inventory): void
    {
        $path = HCFLoader::getInstance()->getDataFolder() . "factionchests/";
        @mkdir($path);

        $contents = [];
        $serializer = new LittleEndianNbtSerializer();

        foreach ($inventory->getContents() as $slot => $item) {
            $tag = $item->nbtSerialize();
            $root = new TreeRoot($tag);
            $contents[$slot] = base64_encode($serializer->write($root));
        }

        file_put_contents($path . $faction . ".json", json_encode($contents, JSON_PRETTY_PRINT));
    }

    public static function loadFactionChest(string $faction, Inventory $inventory): void
    {
        $file = HCFLoader::getInstance()->getDataFolder() . "factionchests/" . $faction . ".json";
        if (!file_exists($file)) return;

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) return;

        $serializer = new LittleEndianNbtSerializer();

        foreach ($data as $slot => $encoded) {
            try {
                $root = $serializer->read(base64_decode($encoded));
                $item = Item::nbtDeserialize($root->mustGetCompoundTag());
                $inventory->setItem((int)$slot, $item);
            } catch (\Throwable) {
                continue;
            }
        }
    }
}