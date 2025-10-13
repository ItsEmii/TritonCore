<?php

namespace hcf\abilities\items;

use hcf\abilities\entity\BallOfRangeEntity;
use hcf\player\Player;
use hcf\utils\Utils;
use hcf\cooldown\Cooldown;
use pocketmine\utils\TextFormat;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

class BallOfRange implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "BallOfRange") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.Ballofrange') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }
                            $entity = new BallOfRangeEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch()), $player);
                            $entity->setMotion($event->getDirectionVector()->multiply(1.5));
                            $entity->spawnToAll();
                            $player->sendMessage("§6Has usado §cBall of Range");
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 8 * 20, 1));
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 8 * 20, 2));
                            $session->addCooldown('ability.Ballofrange', ' §3Ball Of Range&r&7: &c', 30);
                            $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 5);
                            $item->pop();
                            $player->getInventory()->setItemInHand($item);
                        } else {
                            $player->sendMessage("§6Tienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cUsaste Ball Of Range recientemente. Espera " . Cooldown::format($session->getCooldown('ability.Ballofrange')->getTime()));
                    }
                }
            }
    }

    public function onHitByProjectile(ProjectileHitEntityEvent $event) : void
    {
        $hit = $event->getEntityHit();
        if ($hit instanceof Player) {
            $entity = $event->getEntity();
            $player = $entity->getOwningEntity();
            if ($player instanceof Player) {
                if ($entity instanceof BallOfRangeEntity) {
                    if ($player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null) {
                        return;
                    }

                    if ($player->getCurrentClaim() === 'Spawn') {
                        return;
                    }
                    if ($hit->getSession()->getCooldown('starting.timer') !== null || $hit->getSession()->getCooldown('pvp.timer') !== null) {
                        return;
                    }

                    if ($hit->getCurrentClaim() === 'Spawn') {
                        return;
                    }
                    $hit->sendMessage(TextFormat::colorize("§c¡Has sido impactado por una Ball of Range!"));
                    $hit->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 5 * 20, 1));
                    $hit->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 5 * 20, 1));
                }
            }
        }
    }
}