<?php

namespace hcf\block\blocks;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerInteractEvent;

class BrewingStandBlock extends Block
{
    public function onInteract(PlayerInteractEvent $event): void
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName("Estante de Pociones");
        $menu->getInventory()->setSize(27);
        $menu->send($event->getPlayer());
    }
}