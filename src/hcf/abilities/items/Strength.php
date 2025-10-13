<?php

namespace hcf\abilities\items;

use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

class Strength implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "Strength") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.strength') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            if ($player->getSession()->getFaction() !== null) {
                                $players = array_filter($player->getServer()->getOnlinePlayers(), function ($target) use ($player): bool {
                                     return $target instanceof Player && $player->getPosition()->distance($target->getPosition()) <= 10 && $player->getSession()->getFaction() === $target->getSession()->getFaction();
                                });

                                foreach ($players as $target) {
                                    $target->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 10, 1));
                                    $target->sendMessage("§6Has recibido Strength");
                                }
                            }
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 10, 1));
                            $player->sendMessage("§6Has usado Fuerza");
                            $session->addCooldown('ability.strength', ' §cStrength&r&7: &c', 30);
                            $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);
                            $item->pop();
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown en Partner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar Strength por " . Cooldown::format($session->getCooldown("ability.strength")->getTime()));
                    }
                }
            }
    }
}