<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class NinjaShear implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($player instanceof Player)
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "NinjaShear") {
                    $event->cancel();
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.ninjashear') === null) {
                        if ($session->getCooldown('ability.global') === null) {
                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $lastDamageCooldown = $session->getCooldown('lastDamage');
                            if ($lastDamageCooldown === null || $lastDamageCooldown->getTime() > 15) {
                                $player->sendMessage("§cNadie te ha atacado.");
                                return;
                            }

                            $cause = $player->getLastDamageCause();
                            if (!$cause instanceof EntityDamageByEntityEvent || !($damager = $cause->getDamager()) instanceof Player || !$damager->isOnline()) {
                                $player->sendMessage("§cNadie te ha atacado.");
                                return;
                            }

                            $session->addCooldown('ability.ninjashear', ' §cNinja Shear&r&7: &c', 120);
                            $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);
                            $player->sendMessage("§6Has usado §bNinja Shear");
                            $session->addCooldown('teleporting.ninjashear', ' §dTeleportando&r&c: &7', 5);

                            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $damager): void {
                                if ($player->isOnline() && $damager->isOnline() && $player->getSession() !== null && $damager->getSession() !== null) {
                                    $player->teleport($damager->getPosition());
                                    $player->sendMessage("§7Has sido §dteletransportado§7 a§f {$damager->getSession()->getName()}.");
                                    $damager->sendMessage("§f{$player->getSession()->getName()} §7se ha §dteletransportado §7a ti.");
                                } elseif ($player->isOnline()) {
                                    $player->sendMessage("§cFallo en teletransporte: El jugador ya no está en línea.");
                                }
                            }), 20 * 5);

                            $item->pop(); 
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§cTienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar §bNinja Shear aún. Espera §f" . Cooldown::format($session->getCooldown("ability.ninjashear")->getTime()));
                    }
                }
            }
    }
}