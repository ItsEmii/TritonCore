<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class StromBreaker implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $player = $event->getDamager();
        if ($victim instanceof Player && $player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "Strombreaker") {
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.strombreaker') === null) {
                        if ($session->getCooldown('ability.global') === null) {

                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getCooldown('starting.timer') !== null || $victim->getSession()->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getFaction() !== null && $session->getFaction() !== null && $victim->getSession()->getFaction() === $session->getFaction()) {
                                $player->sendMessage(TextFormat::colorize("§eNo puedes atacar a §2" . $victim->getSession()->getName() . "§eporque es de tu faction."));
                                $event->cancel();
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            if (!$player->getArmorInventory()->getHelmet()->isNull()) {
                                $playerClass = $player->getClass();
                                if ($playerClass === null || !in_array($playerClass->getName(), ["Rogue", "Archer", "Bard"])) {
                                    $helmet = $victim->getArmorInventory()->getHelmet();
                                    if ($helmet->isNull()) {
                                        $player->sendMessage("§cEl jugador no tiene casco");
                                        return;
                                    }
                                    $player->sendMessage("§6Has usado §aStromBreaker");
                                    $victim->sendMessage("§l§mTE HAN QUITADO EL CASCO");
                                    $session->addCooldown('ability.strombreaker', ' &eStromBreaker&r&7: &c', 180);
                                    $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 5);

                                    $victim->getArmorInventory()->setHelmet(VanillaBlocks::AIR()->asItem());

                                    HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim, $helmet): void {
                                        if ($victim->isOnline()) {
                                            $victim->getArmorInventory()->setHelmet($helmet);
                                            $victim->sendMessage("§aTu casco ha vuelto a la normalidad");
                                        }
                                    }), 20 * 8);

                                    if ($item->getCount() > 1) {
                                        $item->setCount($item->getCount() - 1);
                                    } else {
                                        $item = VanillaItems::AIR();
                                    }
                                    $player->getInventory()->setItemInHand($item);
                                } else {
                                    $player->sendMessage("§cNo puedes usar StromBreaker con esa clase");
                                }
                            } else {
                                $player->sendMessage("§cNecesitas un casco para usar StromBreaker.");
                            }

                        } else {
                            $player->sendMessage("§6Tienes cooldown en Partner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cYa usaste la StromBreaker espera" . Cooldown::format($session->getCooldown("ability.strombreaker")->getTime()));
                    }
                }
            }
        }
    }
}