<?php

namespace hcf\listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\item\Item;

class DirectPickupListener implements Listener {

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();

        if (!$player instanceof Player || $player->isClosed()) {
            return;
        }

        $block = $event->getBlock();
        $drops = $block->getDrops($player->getInventory()->getItemInHand());

        $event->setDrops([]);

        foreach ($drops as $drop) {
            if ($player->getInventory()->canAddItem($drop)) {
                $player->getInventory()->addItem($drop);
            } else {
                $player->getWorld()->dropItem($player->getPosition(), $drop);
            }
        }
    }
}
