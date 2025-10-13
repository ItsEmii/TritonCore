<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class AbilityDisabler implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $player = $event->getDamager();
        if ($victim instanceof Player && $player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "AbilityDisabler") {
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.abilitydisabler') === null) {
                        if ($session->getCooldown('ability.global') === null) {

                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getCooldown('starting.timer') !== null || $victim->getSession()->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getFaction() !== null && $session->getFaction() !== null && $victim->getSession()->getFaction() === $session->getFaction()) {
                                $player->sendMessage(TextFormat::colorize("§eNo puedes atacar a §2" . $victim->getSession()->getName() . "§e porque está en tu faction."));
                                $event->cancel();
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $victim->getSession()->addCooldown('ability.global', ' §&aAbility Disabler&r&7: &c', 25);
                            $player->sendMessage("§6Has usado §aAbilityDisabler");
                            $victim->sendMessage(TextFormat::colorize("§c¡Has sido atacado por un Ability Disabler!"));
                            $session->addCooldown('ability.abilitydisabler', ' &aAbility Disabler&r&7: &c', 120);
                            $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);

                            if($item->getCount() > 1){
                                $item->setCount($item->getCount() - 1);
                            } else {
                                $item = VanillaItems::AIR();
                            }
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown de §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cYa usaste Ability Disabler recientemente. Espera " . Cooldown::format($session->getCooldown("ability.abilitydisabler")->getTime()));
                    }
                }
            }
        }
    }
}