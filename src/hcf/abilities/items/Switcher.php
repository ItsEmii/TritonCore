<?php

namespace hcf\abilities\items;

use hcf\abilities\entity\SwitcherEntity;
use hcf\cooldown\Cooldown;
use hcf\player\Player;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\Position;

class Switcher implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "Switcher") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.Switcher') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }
                            $entity = new SwitcherEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch()), $player);
                            $entity->setMotion($event->getDirectionVector()->multiply(1.5));
                            $entity->spawnToAll();
                            $player->sendMessage("§6Has usado §aSwitcher");
                            $session->addCooldown('ability.Switcher', ' §aSwitcher&r&7: &c', 60);
                            $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 5);
                            $item->pop();
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown en Partner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar la Switcher por " . Cooldown::format($session->getCooldown("ability.Switcher")->getTime()));
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
                if ($entity instanceof SwitcherEntity) {
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
                    $pos1 = $player->getPosition();
                    $pos2 = $hit->getPosition();
                    if ($pos1 instanceof Position) {
                        $hit->teleport($pos1);
                        $hit->sendMessage("§c¡Has sido cambiado de posición!");
                    }
                     self::playSound($pos1, "mob.endermite.hit");
                     self::playSound($pos2, "mob.endermite.hit");
                    $player->teleport($pos2);
                    $player->sendMessage("§aHas cambiado de posiciones con {$hit->getName()}!");
                }
            }
        }
    }

    protected static function playSound(Position $pos, string $soundName):void {
        $sPk = new PlaySoundPacket();
        $sPk->soundName = $soundName;
        $sPk->x = $pos->x;
        $sPk->y = $pos->y;
        $sPk->z = $pos->z;
        $sPk->volume = $sPk->pitch = 1;
        $pos->getWorld()->broadcastPacketToViewers($pos, $sPk);
    }
}