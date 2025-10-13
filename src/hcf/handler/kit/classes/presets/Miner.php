<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\HCFClass;
use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

/**
 * Class Miner
 * @package hcf\handler\kit\classes\presets
 */
class Miner extends HCFClass
{
    /** @var int */
    private int $minedDiamonds = 0;

    /**
     * Miner construct.
     */
    public function __construct()
    {
        parent::__construct(self::MINER);
    }

    /**
     * @return Item[]
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::IRON_HELMET(),
            VanillaItems::IRON_CHESTPLATE(),
            VanillaItems::IRON_LEGGINGS(),
            VanillaItems::IRON_BOOTS()
        ];
    }

    /**
     * @return EffectInstance[]
     */
    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::HASTE(), 20 * 15, 2),
            new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 20 * 15, 1),
            new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 15, 1)
        ];
    }

    /**
     * @param Player $player
     */
    public function applyInvisibility(Player $player): void
    {
        if ($player->getPosition()->getFloorY() < 50) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 5, 0, false, false));

            //  $player->getScoreboard()->updateLine("invisibility", "§7Invisible: §aYes");
        } else {
            $player->getEffects()->remove(VanillaEffects::INVISIBILITY());
                 //$player->getScoreboard()->updateLine("invisibility", "§7Invisible: §cNo");
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getClass() === null || $player->getClass()->getTypeId() !== self::MINER) {
            return;
        }

        $block = $event->getBlock();
        switch ($block->getTypeId()) {
            case VanillaItems::DIAMOND_ORE()->getTypeId():
            case VanillaItems::DEEPSLATE_DIAMOND_ORE()->getTypeId():
                $this->minedDiamonds++;

                // $player->getScoreboard()->updateLine("mined_diamonds", "§7Diamonds: §b" . $this->minedDiamonds);
                break;

        }
    }

    /**
     * @return int
     */
    public function getMinedDiamonds(): int
    {
        return $this->minedDiamonds;
    }
    public function getName(): string
    {
        return "Archer";
    }
}