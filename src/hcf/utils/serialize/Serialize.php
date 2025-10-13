<?php

namespace hcf\utils\serialize;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

class Serialize {
    
    public static function getItem(string $item): Item {
        return StringToItemParser::getInstance()->parse($item);
    }

    public static function serialize(Item $item) : string {

        return base64_encode(gzcompress(serialize($item->nbtSerialize())));
    }

    public static function deserialize(string $item): Item {
        $itemNBT = unserialize(gzuncompress(base64_decode($item)));
        return Item::nbtDeserialize($itemNBT);
    }

    public static function itemToJson(Item $item) : string {
        return base64_encode(serialize($item->nbtSerialize()));
    }

    public static function jsonToItem(string $json) : Item {
        $itemNBT = unserialize(base64_decode($json));
        return Item::nbtDeserialize($itemNBT);
    }
}