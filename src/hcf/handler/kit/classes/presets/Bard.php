<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\HCFClass;
use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class Bard extends HCFClass
{
    public function __construct()
    {
        parent::__construct(self::BARD);
    }

    public function getArmorItems(): array
    {
        return [
            VanillaItems::GOLDEN_HELMET(),
            VanillaItems::GOLDEN_CHESTPLATE(),
            VanillaItems::GOLDEN_LEGGINGS(),
            VanillaItems::GOLDEN_BOOTS()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 20 * 15, 1),
            new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 15, 0),
            new EffectInstance(VanillaEffects::REGENERATION(), 20 * 15, 0)
        ];
    }

    public function handleItemHeld(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if (!$player instanceof Player || $player->getClass() === null || $player->getClass()->getTypeId() !== HCFClass::BARD || $player->getSession()->getCooldown('bard.cooldown') !== null || $player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null || $player->getCurrentClaim() === 'Spawn') {
            return;
        }

        $server = $player->getServer();
        $position = $player->getPosition();
        $faction = $player->getSession()->getFaction();
        $radius = 20;

        $effect = null;
        $amplifier = 0;

        switch ($item->getTypeId()) {
            case VanillaItems::MAGMA_CREAM()->getTypeId():
                $effect = VanillaEffects::FIRE_RESISTANCE();
                $amplifier = 1;
                break;
            case VanillaItems::INK_SAC()->getTypeId():
                $effect = VanillaEffects::INVISIBILITY();
                break;
            case VanillaItems::BLAZE_POWDER()->getTypeId():
                $effect = VanillaEffects::STRENGTH();
                break;
            case VanillaItems::IRON_INGOT()->getTypeId():
                $effect = VanillaEffects::RESISTANCE();
                break;
            case VanillaItems::SUGAR()->getTypeId():
                $effect = VanillaEffects::SPEED();
                $amplifier = 1;
                break;
            case VanillaItems::FEATHER()->getTypeId():
                $effect = VanillaEffects::JUMP_BOOST();
                $amplifier = 2;
                break;
            case VanillaItems::GHAST_TEAR()->getTypeId():
                $effect = VanillaEffects::REGENERATION();
                $amplifier = 1;
                break;
        }

        if ($effect !== null) {
            $player->getEffects()->add(new EffectInstance($effect, 20 * 7, $amplifier));
            if ($faction !== null) {
                foreach ($server->getOnlinePlayers() as $target) {
                    if ($target instanceof Player && $position->distance($target->getPosition()) <= $radius && $target->getSession()->getFaction() === $faction) {
                        $target->getEffects()->add(new EffectInstance($effect, 20 * 7, $amplifier));
                    }
                }
            }
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if (!$player instanceof Player || $player->getClass() === null || $player->getClass()->getTypeId() !== HCFClass::BARD) {
            return;
        }

        $energy = $player->getSession()->getEnergy('bard.energy');
        if ($energy === null || $player->getSession()->getCooldown('bard.cooldown') !== null || $player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null || $player->getCurrentClaim() === 'Spawn') {
            return;
        }

        $server = $player->getServer();
        $position = $player->getPosition();
        $faction = $player->getSession()->getFaction();
        $radius = 20;

        switch ($item->getTypeId()) {
            case VanillaItems::SPIDER_EYE()->getTypeId():
                if ($energy->getEnergy() < 35) return;
                $this->applyBardEffect($player, VanillaEffects::WITHER(), 1, 35, "Wither II", $radius);
                break;

            case VanillaItems::BLAZE_POWDER()->getTypeId():
                if ($energy->getEnergy() < 40) return;
                $this->applyBardEffect($player, VanillaEffects::STRENGTH(), 1, 40, "Strength II", $radius, $faction);
                break;

            case VanillaItems::IRON_INGOT()->getTypeId():
                if ($energy->getEnergy() < 35) return;
                $this->applyBardEffect($player, VanillaEffects::RESISTANCE(), 2, 35, "Resistance III", $radius, $faction);
                break;

            case VanillaItems::SUGAR()->getTypeId():
                if ($energy->getEnergy() < 20) return;
                $this->applyBardEffect($player, VanillaEffects::SPEED(), 2, 20, "Speed III", $radius, $faction);
                break;

            case VanillaItems::FEATHER()->getTypeId():
                if ($energy->getEnergy() < 30) return;
                $this->applyBardEffect($player, VanillaEffects::JUMP_BOOST(), 7, 30, "Jump Boost VIII", $radius, $faction);
                break;

            case VanillaItems::GHAST_TEAR()->getTypeId():
                if ($energy->getEnergy() < 35) return;
                $this->applyBardEffect($player, VanillaEffects::REGENERATION(), 2, 35, "Regeneration III", $radius, $faction);
                break;
        }

        $item->pop();
        $player->getInventory()->setItemInHand($item);
    }

    private function applyBardEffect(Player $player, \pocketmine\entity\effect\Effect $effect, int $amplifier, int $cost, string $name, int $radius, ?string $faction = null): void
    {
        $player->getEffects()->add(new EffectInstance($effect, 20 * 7, $amplifier));
        $server = $player->getServer();
        $pos = $player->getPosition();

        if ($faction !== null) {
            foreach ($server->getOnlinePlayers() as $target) {
                if ($target instanceof Player && $pos->distance($target->getPosition()) <= $radius && $target->getSession()->getFaction() === $faction) {
                    $target->getEffects()->add(new EffectInstance($effect, 20 * 7, $amplifier));
                    $target->sendMessage(TextFormat::colorize("&eEl Bard de tu faction (&a" . $player->getName() . "&e) ha usado &4" . $name));
                }
            }
        } else {
            foreach ($server->getOnlinePlayers() as $target) {
                if ($target instanceof Player && $pos->distance($target->getPosition()) <= $radius) {
                    $target->getEffects()->add(new EffectInstance($effect, 20 * 7, $amplifier));
                    $target->sendMessage(TextFormat::colorize("&eEl Bard (&a" . $player->getName() . "&e) ha usado &4" . $name));
                }
            }
        }

        $player->getSession()->addCooldown('bard.cooldown', ' &eEfecto Bard&r&7: &r&7', 10);
        $player->getSession()->getEnergy('bard.energy')?->reduceEnergy($cost);
    }

    public function getName(): string
    {
        return "Bard";
    }
}