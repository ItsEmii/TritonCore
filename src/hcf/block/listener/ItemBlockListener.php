<?php

namespace hcf\block\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\item\Potion;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class ItemBlockListener implements Listener
{
    public function onUse(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();

        if ($this->isBlockedPotion($item)) {
            $event->cancel();
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void
    {
        $item = $event->getItem();

        if ($this->isBlockedPotion($item)) {
            $event->cancel();
        }
    }

    private function isBlockedPotion($item): bool
    {
        if (!$item instanceof Potion) {
            return false;
        }

        $tag = $item->getNamedTag();

        if (!$tag instanceof CompoundTag) {
            return false;
        }

        if (!$tag->hasTag("Potion")) {
            return false;
        }

        $potionId = $tag->getString("Potion");

        $blockedPotions = [
            "minecraft:strength",
            "minecraft:strong_strength",
            "minecraft:long_strength"
        ];

        return in_array($potionId, $blockedPotions, true);
    }
}