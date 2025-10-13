<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class GuardianAngel implements Listener
{
    private array $activePlayers = [];

    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "GuardianAngel") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.GuardianAngel') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $session->addCooldown('ability.GuardianAngel', ' §bGuardianAngel&r&7: &c', 120);
                            $session->addCooldown('ability.global', ' &5Partner Item&r&7: &7', 5);
                            $player->sendMessage("§6Has activado §bGuardian Angel.");
                            $this->activePlayers[$player->getName()] = $player;

                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                                if ($player->isOnline() && isset($this->activePlayers[$player->getName()])) {
                                    unset($this->activePlayers[$player->getName()]);
                                    $player->sendMessage("§cEl Guardian Angel ha expirado.");
                                } elseif (!$player->isOnline() && isset($this->activePlayers[$player->getName()])) {
                                    unset($this->activePlayers[$player->getName()]);
                                }
                            }), 20 * 60);

                            $item->pop();
                            $player->getInventory()->setItemInHand($item);
                        } else {
                            $player->sendMessage("§6Tienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar Guardian Angel aún. Espera " . Cooldown::format($session->getCooldown("ability.GuardianAngel")->getTime()));
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Player && $entity->isOnline() && isset($this->activePlayers[$entity->getName()])) {
            if ($entity->getHealth() - $event->getFinalDamage() <= 4) {
                $entity->setHealth($entity->getMaxHealth());
                $entity->sendMessage(TextFormat::colorize("&a¡Guardian Angel ha restaurado tu vida!"));
                unset($this->activePlayers[$entity->getName()]);
            }
        }
    }
}