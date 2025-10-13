<?php

 namespace hcf\addons\modules;

use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\entity\projectile\EnderPearl;
use hcf\player\Player;

class EnderPearlDamage implements Listener {

    public function onProjectileHit(ProjectileHitEvent $event): void {
        $projectile = $event->getEntity();

        if ($projectile instanceof EnderPearl) {
            $shooter = $projectile->getOwningEntity();

            if ($shooter instanceof Player) {
                $shooter->setHealth($shooter->getHealth() - 3);
            }
        }
    }
}