<?php

namespace hcf\abilities\items;

use hcf\abilities\entity\PortableBardEntity;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerInteractEvent;
use JetBrains\PhpStorm\Pure;

class PortableBard implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if (!$player instanceof Player || !$item->getNamedTag()->getTag("Abilities")) {
            return;
        }

        if ($item->getNamedTag()->getString("Abilities") !== "PortableBard") {
            return;
        }

        $event->cancel();
        $session = $player->getSession();

        if ($session->getCooldown('ability.portablebard') !== null) {
            $player->sendMessage("§cYa usaste §dPortable Bard§c hace §f" . Cooldown::format($session->getCooldown("ability.portablebard")->getTime()));
            return;
        }

        if ($session->getCooldown('ability.global') !== null) {
            $player->sendMessage("§cTienes cooldown del §dPartner Item §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
            return;
        }

        if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
            return;
        }

        if ($player->getCurrentClaim() === 'Spawn') {
            return;
        }

        $bard = new PortableBardEntity($player->getLocation());
        $bard->setOwner($player);
        $bard->spawnToAll();

        HCFLoader::$bard_allow[$player->getName()] = true;

        $player->sendMessage("§6Has usado §dPortable Bard");
        $session->addCooldown('ability.portablebard', ' §dPortable Bard&r&7: &c', 240);
        $session->addCooldown('ability.global', ' §5Partner Item&r&7: &c', 5);

        $item->pop();
        $player->getInventory()->setItemInHand($item);
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if ($item->getNamedTag()->getTag("Abilities") !== null && $item->getNamedTag()->getString("Abilities") === "PortableBard") {
            $event->cancel();
        }
    }

    #[Pure]
    public static function isAllow(Player $player): bool
    {
        return isset(HCFLoader::$bard_allow[$player->getName()]);
    }

    public static function setAllow(Player $player): void
    {
        HCFLoader::$bard_allow[$player->getName()] = true;
    }

    public static function removeAllow(Player $player): void
    {
        unset(HCFLoader::$bard_allow[$player->getName()]);
    }
}