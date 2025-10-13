<?php

namespace hcf\block\listener;

use hcf\HCFLoader;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\BlockLegacyIds;

class BlockInteractListener implements Listener
{
    public function onInteract(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        $blockId = $block->getId();

        if ($blockId === BlockLegacyIds::BREWING_STAND) {
            $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
            $menu->setName("Estante de Pociones");
            $menu->getInventory()->setSize(27);
            $menu->send($player);
            $event->cancel();
        }
    }
}