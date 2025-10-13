<?php

namespace hcf\abilities\items;

use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use hcf\cooldown\Cooldown;

class FlowerTank implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "FlowerTank") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.flowertank') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 3, 3));
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 3, 2));
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::ABSORPTION(), 20 * 3, 1));
                            $player->sendMessage("§6Has usado §4Flower Tank");
                            $session->addCooldown('ability.flowertank', ' §4Flower Tank&r&7: &c', 45);
                            $session->addCooldown('ability.global', ' &5Partner Item&r&7: &7', 5);
                            $item->pop();
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar Flower Tank aún. Espera " . Cooldown::format($session->getCooldown("ability.flowertank")->getTime()));
                    }
                }
            }
    }
}