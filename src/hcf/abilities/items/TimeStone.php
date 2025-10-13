<?php

namespace hcf\abilities\items;

use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

class TimeStone implements Listener
{
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if ($player instanceof Player && $item->getNamedTag()->getTag("Abilities") !== null && $item->getNamedTag()->getString("Abilities") === "TimeStone") {
            $event->cancel();
            $session = $player->getSession();
            if ($session->getCooldown('ability.timestone') === null) {
                $this->removeAllCooldowns($player);

                $player->sendMessage("§6¡Se han eliminado todos los cooldowns de los Abilities!");
                $session->addCooldown('ability.timestone', ' &2Time Stone&r&7: &c', 300);

                $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 15);

                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage("§cYa usaste la TimeStore espera " . Cooldown::format($session->getCooldown("ability.timestone")->getTime()));
            }
        }
    }

    private function removeAllCooldowns(Player $player): void
    {
        $session = $player->getSession();
        $cooldowns = $session->getCooldowns();
        foreach ($cooldowns as $key => $cooldown) {
            if (strpos($key, 'ability.') === 0) {
                $session->removeCooldown($key);
            }
        }
    }
}