<?php

declare(strict_types=1);

namespace hcf\abilities\entity;

use hcf\HCFLoader;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\EntityFactory;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\EntityDataHelper;

final class PortableMagueEntity extends Living
{
    private ?string $ownerName = null;
    private int $countdown = 120;
    private int $lastUpdateTime = 0;
    private Vector3 $spawnPosition;

    public static function getNetworkTypeId(): string
    {
        return EntityIds::WITCH;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.6);
    }

    public function getName(): string
    {
        return "Mague";
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setMaxHealth(100);
        $this->setHealth(100);
        $this->setNameTagAlwaysVisible(true);
        $this->setCanSaveWithChunk(true);

        if ($nbt->getTag("Owner") !== null) {
            $this->ownerName = $nbt->getString("Owner");
        }
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        if ($this->ownerName !== null) {
            $nbt->setString("Owner", $this->ownerName);
        }
        return $nbt;
    }

    public function spawnTo(Player $player): void
    {
        parent::spawnTo($player);
        $this->updateNameTag();
    }

    private function updateNameTag(): void
    {
        $owner = $this->getOwner();
        $ownerDisplayName = $owner ? $owner->getName() : "Desconocido";

        $this->setNameTag(TextFormat::colorize("&cPortableMague\n&g&7Dueño: &f" . $ownerDisplayName . "\n&bTiempo: &e" . $this->countdown . "s"));
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $this->setNoClientPredictions();

        $currentTime = time();

        if ($currentTime - $this->lastUpdateTime >= 1) {
            $this->lastUpdateTime = $currentTime;

            if ($this->ownerName === null) {
                $this->flagForDespawn();
                return true;
            }

            if (--$this->countdown <= 0) {
                $this->flagForDespawn();
                return true;
            }

            $owner = $this->getOwner();
            if ($owner === null || !$owner->isOnline()) {
                $this->flagForDespawn();
                return true;
            }

            $this->updateNameTag();
            $this->handleNearbyEntities();
        }

        if (isset($this->spawnPosition)) {
            $this->teleport($this->spawnPosition);
        }
        return $hasUpdate;
    }

    private function handleNearbyEntities(): void
    {
        $factionManager = HCFLoader::getInstance()->getFactionManager();
        $ownerFaction = $factionManager->getFaction($this->ownerName);
        $nearbyEntities = $this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(10, 10, 10));

        foreach ($nearbyEntities as $entity) {
            if ($entity instanceof Player && $entity->getName() !== $this->ownerName) {
                $entityFaction = $factionManager->getFaction($entity->getName());

                if ($ownerFaction !== null && $entityFaction !== null && $ownerFaction->equals($entityFaction)) {
                    continue;
                }

                if ($entity->getSession() !== null && ($entity->getSession()->getCooldown('pvp.timer') !== null || $entity->getSession()->getCooldown('starting.timer') !== null)) {
                    continue;
                }

                $this->applyEffects($entity);
            }
        }
    }

    private function applyEffects(Player $player): void
    {
        if ($this->countdown <= 50 && $this->countdown > 40) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 3, 2, false));
        } elseif ($this->countdown <= 40 && $this->countdown > 30) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::MINING_FATIGUE(), 20 * 3, 2, false));
        } elseif ($this->countdown <= 30 && $this->countdown > 20) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 20 * 3, 2, false));
        } elseif ($this->countdown <= 20 && $this->countdown > 10) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 20 * 3, 1, false));
        } elseif ($this->countdown <= 10 && $this->countdown > 0) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * 3, 1, false));
        }
    }

    public function setOwner(Player $player): void
    {
        $this->ownerName = $player->getName();
        $this->updateNameTag();
    }

    public function setSpawnPosition(Position $pos): void
    {
        $this->spawnPosition = $pos->asVector3();
    }

    public function getOwner(): ?Player
    {
        if ($this->ownerName === null) {
            return null;
        }
        return HCFLoader::getInstance()->getServer()->getPlayerExact($this->ownerName);
    }

    public function flagForDespawn(): void
    {
        parent::flagForDespawn();
    }

    public static function createEntity(): void
    {
        EntityFactory::getInstance()->register(self::class, function(World $world, CompoundTag $nbt): PortableMagueEntity {
            return new PortableMagueEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['PortableMague']);
    }
}