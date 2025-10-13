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
use hcf\utils\Utils;

class RichBill implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $player = $event->getDamager();
        if ($victim instanceof Player && $player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "RichBill") {
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.RichBill') === null) {
                        if ($session->getCooldown('ability.global') === null) {

                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getCooldown('starting.timer') !== null || $victim->getSession()->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getFaction() !== null && $session->getFaction() !== null && $victim->getSession()->getFaction() === $session->getFaction()) {
                                $player->sendMessage("§b §7No puedes dañar a §b" . $victim->getSession()->getName());
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
                                    $player->sendMessage("§7Has usado §9RichBill");
                                    $victim->sendMessage("§l§mTE HAN QUITADO EL CASCO");
                                    $session->addCooldown('ability.RichBill', ' §9RichBill: §c', 300);
                                    $session->addCooldown('ability.global', ' §5Partner Item: §c', 5);

                                    $victim->getArmorInventory()->setHelmet(VanillaBlocks::CARVED_PUMPKIN()->asItem());

                                    HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim, $helmet): void {
                                        if ($victim->isOnline()) {
                                            $victim->getArmorInventory()->setHelmet($helmet);
                                            $victim->sendMessage("§b» §aTu casco volvió a la normalidad.");
                                        }
                                    }), 20 * 8);

                                    if ($item->getCount() > 1) {
                                        $item->setCount($item->getCount() - 1);
                                    } else {
                                        $item = VanillaItems::AIR();
                                    }
                                    $player->getInventory()->setItemInHand($item);
                                } else {
                                    $player->sendMessage("§cNo puedes usar RichBill en ninguna clase");
                                }
                            } else {
                                $player->sendMessage("§cNo tiene casco.");
                            }

                        } else {
                            $player->sendMessage("§cTienes cooldown en Partner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cYa usaste el §9RichBill§r espera §f" . Cooldown::format($session->getCooldown("ability.RichBill")->getTime()));
                    }
                }
            }
        }
    }
}