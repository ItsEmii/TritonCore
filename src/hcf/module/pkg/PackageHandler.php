<?php

namespace hcf\module\pkg;

use hcf\module\pkg\util\Content;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ChestOpenSound;

class PackageHandler implements Listener {

    public function handlePlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($item->getNamedTag()->getTag('ppackage') !== null) {
            $event->cancel();
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if ($item->getNamedTag()->getTag('ppackage') !== null) {
            $content = Content::getInstance()->getItems();
            if (count($content) === 0) {
            	$player->sendMessage(TextFormat::colorize('&cThere is no content in the database.'));
            	return;
            }
            $reward = $content[array_rand($content)];

            if (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage(TextFormat::colorize('&cInventory full.'));
                return;
            }
            $item->pop();
            $player->getWorld()->addSound($player->getPosition(), new ChestOpenSound());
            $player->getInventory()->setItemInHand($item);
            $player->getInventory()->addItem($reward);
            $player->sendMessage(TextFormat::colorize("&l&7[&5Legends&7] &r&7| &ftu haz ganado " . $reward->getCount() . ' ' . ($reward->hasCustomName() ? $reward->getCustomName() : $reward->getName())));
        }
    }
}
