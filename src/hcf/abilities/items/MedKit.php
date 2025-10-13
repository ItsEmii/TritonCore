<?php

namespace hcf\abilities\items;

use hcf\player\Player;
use hcf\cooldown\Cooldown;
use hcf\utils\Utils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\world\sound\TotemUseSound;

class MedKit implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "MedKit") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.MedKit') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $effectManager = $player->getEffects();
                            $world = $player->getWorld();

                            $effectManager->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 6, 4));
                            $effectManager->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 6, 4));
                            $effectManager->add(new EffectInstance(VanillaEffects::ABSORPTION(), 20 * 60, 1));
                            // $world->addSound($player->getPosition(), new TotemUseSound(), [$player]);

                            $player->sendMessage("§7Has usado §bMedKit");
                            // Utils::PlaySound($player, "random.orb", 1, 1);
                            $session->addCooldown('ability.MedKit', ' §bMedKit: §c', 180);
                            $session->addCooldown('ability.global', ' §5PartnerItem: §c', 5);
                            $item->pop();
                            $player->getInventory()->setItemInHand($item);
                        } else {
                            $player->sendMessage("§cTienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar §bMedKit aún. Espera §f" . Cooldown::format($session->getCooldown("ability.MedKit")->getTime()));
                    }
                }
            }
    }
}