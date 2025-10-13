<?php

declare(strict_types=1);

namespace hcf\module\blockshop\entity;

use hcf\module\blockshop\utils\ShopAndSell;
use hcf\player\Player;
use hcf\module\blockshop\utils\Utils;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class ShopAndSellEntity extends Human
{

    /**
     * @param CompoundTag $nbt
     */
    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setForceMovementUpdate(false);
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag(TextFormat::colorize(" \n&6Shop\n&aNEW"));
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if ($damager instanceof Player) {
                if ($damager->hasPermission('npc.command') && $damager->getInventory()->getItemInHand()->getCustomName() === "§eRemove NPC §r§7(Right Click)") {
                    $this->kill();
                    return;
                }
                ShopAndSell::Shop($damager);
            }
        }
    }
}