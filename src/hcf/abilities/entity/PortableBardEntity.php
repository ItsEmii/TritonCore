<?php

declare(strict_types=1);

namespace hcf\abilities\entity;

use hcf\HCFLoader;
use hcf\abilities\items\PortableBard;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Zombie;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\Position;

class PortableBardEntity extends Zombie
{
    private ?string $owner = null;
    private int $count_down = 50;
    private int $time = 0;
    private Position $pos;

    public function __construct(Position $pos, ?string $owner = null)
    {
        parent::__construct($pos);
        $this->pos = $pos;
        $this->owner = $owner;

        $this->setMaxHealth(100);
        $this->setHealth(100);
    }

    public function getName(): string
    {
        return "Bard";
    }

    public function spawnToAll(): void
    {
        parent::spawnToAll();
        $this->setNameTagAlwaysVisible(true);
        $this->setCanSaveWithChunk(true);
        $owner = HCFLoader::getInstance()->getServer()->getPlayerExact($this->owner);
        if ($owner instanceof Player) {
            $this->setNameTag("§d§lPortable Bard\n§dDueño§7: §f" . $owner->getName());
        } else {
            $this->setNameTag("§d§lPortable Bard\n§dDueño§7: §fDesconocido");
        }
        $this->getArmorInventory()->setHelmet(VanillaItems::GOLDEN_HELMET());
        $this->getArmorInventory()->setChestplate(VanillaItems::GOLDEN_CHESTPLATE());
        $this->getArmorInventory()->setLeggings(VanillaItems::GOLDEN_LEGGINGS());
        $this->getArmorInventory()->setBoots(VanillaItems::GOLDEN_BOOTS());
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->time === 0 || time() - $this->time >= 1) {
            $this->time = time();
            if ($this->owner === null) {
                $this->close();
                return parent::onUpdate($currentTick);
            }

            $owner = HCFLoader::getInstance()->getServer()->getPlayerExact($this->owner);

            if (!$owner instanceof Player) {
                return parent::onUpdate($currentTick);
            }

            $faction = $owner->getSession()->getFaction();
            if ($faction !== null) {
                $nearbyFactionMembers = [];
                foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(10, 10, 10)) as $entity) {
                    if (
                        $entity instanceof Player &&
                        $entity->getSession()->getFaction() === $faction &&
                        $this->getPosition()->distance($entity->getPosition()) <= 10
                    ) {
                        $nearbyFactionMembers[] = $entity;
                    }
                }

                foreach ($nearbyFactionMembers as $member) {
                    $member->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 40, 1, false));
                    $member->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 40, 1, false));
                    $member->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 40, 1, false));
                }
            }

            if (PortableBard::isAllow($owner)) {
                if ($this->count_down > 35) {
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 40, 1, false));
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 40, 1, false));
                } elseif ($this->count_down > 20) {
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 40, 1, false));
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 40, 2, false));
                } elseif ($this->count_down > 10) {
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 40, 0, false));
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 40, 1, false));
                } elseif ($this->count_down > 5) {
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 40, 7, false));
                } elseif ($this->count_down > 0) {
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 40, 1, false));
                    $owner->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 40, 1, false));
                }
            }

            if ($this->count_down > 0) {
                $this->count_down--;
            } else {
                $this->close();
            }
        }

        $this->teleport($this->pos);
        return parent::onUpdate($currentTick);
    }

    public function setOwner(Player $player): void
    {
        $this->owner = $player->getName();
    }

    public function setPos(Position $pos): void
    {
        $this->pos = $pos;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }
}