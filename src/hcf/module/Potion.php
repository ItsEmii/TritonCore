<?php

namespace hcf\module;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\PotionType;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileHitBlockEvent;

class Potion Implements Listener
{
    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onHit(ProjectileHitBlockEvent $event): void{
        $projectile = $event->getEntity();

        if($projectile instanceof SplashPotion && $projectile->getPotionType()->equals(PotionType::STRONG_HEALING())){
            $player = $projectile->getOwningEntity();

            if($player instanceof Player){
                $distance = $projectile->getPosition()->distance($player->getPosition());

                if($distance <= 4.5 && $player->isAlive()){
                    $health = $player->getHealth() + 5.3;
                    $player->setHealth($health > $player->getMaxHealth() ? $player->getMaxHealth() : $health);
                }
            }
        }
    }

    /**
     * @handleCancelled true
     */
    public function onInteract(PlayerInteractEvent $event): void{
        $item = $event->getItem();

        if($item instanceof \pocketmine\item\SplashPotion){
            $player = $event->getPlayer();
            $entity = new SplashPotion(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->yaw, $player->getLocation()->pitch), $player, PotionType::STRONG_HEALING());
            $entity->setMotion($player->getDirectionVector()->multiply($item->getThrowForce()));
            $entity->spawnToAll();
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }
    }

}
