<?php

declare(strict_types=1);

namespace hcf\player\disconnected;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use hcf\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\Server;
use pocketmine\world\World;

class DisconnectedManager
{

    /**
     * DisconnectedManager construct.
     * @param Disconnected[] $disconnected
     */
    public function __construct(
        private array $disconnected = []
    ) {
        EntityFactory::getInstance()->register(LogoutMob::class, function(World $world, CompoundTag $nbt): LogoutMob {
            return new LogoutMob(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['DisconnectedMob', 'hcf:disconnectedmob'], EntityIds::VILLAGER);
        $this->despawnMobs();
    }

    public function onDisable(): void
    {
        $this->despawnMobs();
    }

    private function despawnMobs(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getDefaultWorld()->getEntities() as $entity) {
            if ($entity instanceof LogoutMob)
                $entity->flagForDespawn();
        }
    }

    /**
     * @return Disconnected[]
     */
    public function getAllDisconnected(): array
    {
        return $this->disconnected;
    }

    /**
     * @param string $xuid
     * @return Disconnected|null
     */
    public function getDisconnected(string $xuid): ?Disconnected
    {
        return $this->disconnected[$xuid] ?? null;
    }

    /**
     * @param Player $player
     */
    public function addDisconnected(Player $player): void
    {
        $this->disconnected[(string)$player->getUniqueId()] = new Disconnected($player, (string)$player->getUniqueId(), $player->getName(), $player->getHealth(), $player->getLocation());
    }

    /**
     * @param string $xuid
     */
    public function removeDisconnected(string $xuid): void
    {
        unset($this->disconnected[$xuid]);
    }
}