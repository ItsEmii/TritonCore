<?php

namespace hcf\addons\modules;

use hcf\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class OreBreak implements Listener
{
    private array $validOres = [];

    public function __construct()
    {
        $this->validOres = [
            VanillaBlocks::DIAMOND_ORE(),
            VanillaBlocks::DEEPSLATE_DIAMOND_ORE(),
            VanillaBlocks::IRON_ORE(),
            VanillaBlocks::DEEPSLATE_IRON_ORE(),
            VanillaBlocks::GOLD_ORE(),
            VanillaBlocks::DEEPSLATE_GOLD_ORE(),
            VanillaBlocks::EMERALD_ORE(),
            VanillaBlocks::DEEPSLATE_EMERALD_ORE(),
            VanillaBlocks::COAL_ORE(),
            VanillaBlocks::DEEPSLATE_COAL_ORE(),
            VanillaBlocks::LAPIS_LAZULI_ORE(),
            VanillaBlocks::DEEPSLATE_LAPIS_LAZULI_ORE(),
            VanillaBlocks::REDSTONE_ORE(),
            VanillaBlocks::DEEPSLATE_REDSTONE_ORE(),
            VanillaBlocks::COPPER_ORE(),
            VanillaBlocks::DEEPSLATE_COPPER_ORE()
        ];
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;

        $block = $event->getBlock();

        foreach ($this->validOres as $ore) {
            if ($block->getTypeId() === $ore->getTypeId()) {
                $event->cancel();
                $block->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());

                $current = $player->getSession()->getCrystals();
                $player->getSession()->setCrystals($current + 1);

                $player->sendMessage("§l§a+1 Coin");
                break;
            }
        }
    }
}