<?php

declare(strict_types=1);

namespace hcf\timer;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\player\disconnected\DisconnectedMob;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

class TimerListener implements Listener
{
    public function handleDamage(EntityDamageEvent $event): void
    {
        if ($event->isCancelled()) return;

        $entity = $event->getEntity();
        $sotw = HCFLoader::getInstance()->getTimerManager()->getSotw();

        if ($entity instanceof DisconnectedMob && $sotw->isActive()) {
            $event->cancel();
            return;
        }


        if ($entity instanceof Player) {

            if ($event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player) {
                $attacker = $event->getDamager();

                if ($sotw->isActive() && !$sotw->isDisabled($attacker)) {
                    $attacker->sendMessage("§cNo puedes atacar mientras estás en SOTW.");
                    $event->cancel();
                    return;
                }

                if ($sotw->isActive() && !$sotw->isDisabled($entity)) {
                    $attacker->sendMessage("§cEste jugador está protegido por SOTW.");
                    $event->cancel();
                    return;
                }
            }

            if ($sotw->isActive() && !$sotw->isDisabled($entity)) {
                $event->cancel();
            }
        }
    }
}