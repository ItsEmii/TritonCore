<?php

namespace hcf\abilities\items;

use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\VanillaItems;
use pocketmine\item\PotionType;

class PotionRefill implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if (!$player instanceof Player) return;
        if ($item->getNamedTag()->getTag("Abilities") === null) return;
        if ($item->getNamedTag()->getString("Abilities") !== "Potion") return;

        $event->cancel();
        $session = $player->getSession();

        if ($session->getCooldown('ability.Potion') !== null) {
            $player->sendMessage("§cNo puedes usar §4Potion Refill§c por " . Cooldown::format($session->getCooldown("ability.Potion")->getTime()));
            return;
        }

        if ($session->getCooldown('ability.global') !== null) {
            $player->sendMessage("§6Tienes cooldown de §f" . Cooldown::format($session->getCooldown("ability.global")->getTime()));
            return;
        }

        if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
            return;
        }

        if ($player->getCurrentClaim() === 'Spawn') {
            return;
        }

        $potion = VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING);
        $potion->setCount(36);

        $player->getInventory()->addItem($potion);
        $player->sendMessage("§6Has usado §4Potion Refill");

        $session->addCooldown('ability.Potion', ' §4PotionRefill: &c', 60);
        $session->addCooldown('ability.global', ' §5Partner Items&r&7: &c', 5);

        $item->pop();
        $player->getInventory()->setItemInHand($item);
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }
}