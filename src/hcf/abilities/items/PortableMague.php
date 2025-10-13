<?php

namespace hcf\abilities\items;

use hcf\abilities\entity\PortableMagueEntity;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;

class PortableMague implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if (!$player instanceof Player || !$item->getNamedTag()->getTag("Abilities")) {
            return;
        }

        if ($item->getNamedTag()->getString("Abilities") !== "PortableMague") {
            return;
        }

        $event->cancel();
        $session = $player->getSession();

        if ($session->getCooldown('ability.portablemague') !== null) {
            $player->sendMessage("§cYa usaste §4Portable Mague§c hace §f" . Cooldown::format($session->getCooldown("ability.portablemague")->getTime()));
            return;
        }

        if ($session->getCooldown('ability.global') !== null) {
            $player->sendMessage("§cTienes cooldown en §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
            return;
        }

        if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
            return;
        }

        if ($player->getCurrentClaim() === 'Spawn') {
            return;
        }

        $mague = new PortableMagueEntity($player->getLocation());
        $mague->setOwner($player);
        $mague->setSpawnPosition($player->getPosition());
        $mague->spawnToAll();

        $session->addCooldown('ability.portablemague', ' §4Portable Mague&r&7: &c', 240);
        $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);

        $item->pop();
        $player->getInventory()->setItemInHand($item);
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if ($player instanceof Player && $item->getNamedTag()->getTag("Abilities") !== null && $item->getNamedTag()->getString("Abilities") === "PortableMague") {
            $event->cancel();
        }
    }
}