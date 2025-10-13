<?php

namespace hcf\addons\modules;

use hcf\player\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\TextFormat;

class SwordStats implements Listener
{
    /**
     * @param PlayerDeathEvent $event
     */
    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $entity = $event->getPlayer();
        if (!$entity instanceof Player) {
            return;
        }
        $cause = $entity->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            $item = $damager->getInventory()->getItemInHand();
            $nbt = $item->getNamedTag();

            if ($nbt->getTag("kills") !== null) {
                $nbt->setInt("kills", $nbt->getTag("kills")->getValue() + 1);
            } else {
                $nbt->setInt("kills", 1);
            }
            $item->setNamedTag($nbt);

            $oldLore = $item->getLore();

            if ($item->getNamedTag()->getTag("kills") !== null) {
                $kills = $item->getNamedTag()->getInt("kills", 1);
            } else {
                $kills = 1;
            }

            $oldLore[0] = "";
            $oldLore[1] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "§rKills" . TextFormat::RESET . ": " . TextFormat::WHITE . $kills;

            foreach ($oldLore as $key => $value) {
                if ($key === 1 || $key === 0) continue;
                $oldLore[$key + 1] = $value;
            }

            $oldLore[2] = TextFormat::RESET . TextFormat::BLUE . $damager->getName() . TextFormat::YELLOW . "§r killed " . TextFormat::RED . $entity->getName();
            $newLore = array_splice($oldLore, 0, 7, true);
            $item->setLore($newLore);
            $damager->getInventory()->setItemInHand($item);
        }
    }

}