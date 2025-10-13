<?php

namespace hcf\abilities\items;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class ExoticBone implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $player = $event->getDamager();
        if ($victim instanceof Player && $player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if ($item->getNamedTag()->getTag("Abilities") !== null) {
                if ($item->getNamedTag()->getString("Abilities") === "ExoticBone") {
                    $session = $player->getSession();
                    if ($session->getCooldown('ability.exoticbone') === null) {
                        if ($session->getCooldown('ability.global') === null) {

                            if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getCooldown('starting.timer') !== null || $victim->getSession()->getCooldown('pvp.timer') !== null) {
                                return;
                            }

                            if ($victim->getSession()->getFaction() !== null && $session->getFaction() !== null && $victim->getSession()->getFaction() === $session->getFaction()) {
                                $player->sendMessage(TextFormat::colorize("§eNo puedes atacar a §2" . $victim->getSession()->getName() . "§eporque es de tu faction"));
                                $event->cancel();
                                return;
                            }

                            if ($player->getCurrentClaim() === 'Spawn') {
                                return;
                            }

                            $victim->getSession()->addCooldown('ability.exoticbonetag', ' &aAntiTrapper Bone&r&7: &c', 20);
                            $player->sendMessage("§6Has usado §aAntiTrapper Bone");
                            $victim->sendMessage(TextFormat::colorize("§c¡Has sido atacado por un AntiTrapper Bone!"));
                            $session->addCooldown('ability.exoticbone', ' §9Exotic Bone&r&7: &c', 120);
                            $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 5);

                            if($item->getCount() > 1){
                                $item->setCount($item->getCount() - 1);
                            } else {
                                $item = VanillaItems::AIR();
                            }
                            $player->getInventory()->setItemInHand($item);

                        } else {
                            $player->sendMessage("§6Tienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
                        }
                    } else {
                        $player->sendMessage("§cNo puedes usar AntiTrapper Bone aún. Espera " . Cooldown::format($session->getCooldown("ability.exoticbone")->getTime()));
                    }
                }
            }
        }
    }

    public function interactBlocks(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof Player && $player->getSession()->getCooldown('ability.exoticbonetag') !== null) {
            $event->cancel();
        }
    }

    public function breakBocks(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof Player && $player->getSession()->getCooldown('ability.exoticbonetag') !== null) {
            $event->cancel();
        }
    }

    public function placeBlocks(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof Player && $player->getSession()->getCooldown('ability.exoticbonetag') !== null) {
            $event->cancel();
        }
    }
}