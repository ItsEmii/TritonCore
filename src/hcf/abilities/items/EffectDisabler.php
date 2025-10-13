<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\event\block\BlockBreakEvent;
use hcf\cooldown\Cooldown;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class EffectDisabler implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $player = $event->getDamager();
        if ($victim instanceof Player && $player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "EffectDisabler") {
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.effectdisabler') === null) {
                        if ($session->getCooldown('ability.global') === null) {

                            $effects = $victim->getEffects()->all();

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

                            if (count($effects) <= 0) {
                                $player->sendMessage("§cEl jugador " . $victim->getName() . " no tiene efectos.");
                                return;
                            }

                            $victim->getEffects()->clear();
                            $player->sendMessage("§6Has usado §aEffects Disabler en " . $victim->getName());
                            $victim->sendMessage(TextFormat::colorize("§c¡Tus efectos han sido desactivados temporalmente!"));

                            $session->addCooldown('ability.effectdisabler', ' §aEffects Disabler&r&7:&c', 120);
                            $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);

                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 0.5);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 1.5);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 2);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 2.5);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 3);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim): void {
                                if ($victim->isOnline()) {
                                    $victim->getEffects()->clear();
                                }
                            }), 20 * 3.5);
                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim, $effects): void {
                                if ($victim->isOnline()) {
                                    foreach ($effects as $e) {
                                        $victim->getEffects()->add($e);
                                    }
                                }
                            }), 20 * 4);

                            if ($item->getCount() > 1) {
                                $item->setCount($item->getCount() - 1);
                            } else {
                                $item = VanillaItems::AIR();
                            }
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar Effects Disabler aún. Espera " . Cooldown::format($session->getCooldown("ability.effectdisabler")->getTime()));
                    }
                }
            }
        }
    }
}