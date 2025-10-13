<?php

declare(strict_types=1);

namespace hcf\player\disconnected;

use hcf\HCFLoader;
use hcf\player\Player as PlayerPlayer;
use hcf\session\Session;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\player\Player;
use hcf\cooldown\Cooldown;
use pocketmine\utils\TextFormat;

class Disconnected
{

    /**
     * Disconnected construct.
     * @param string $xuid
     * @param string $name
     * @param float $health
     * @param Location $location
     * @param Item[] $inventory
     * @param Item[] $armorInventory
     * @param LogoutMob|null $disconnectedMob
     */
    public function __construct(
        private PlayerPlayer $player,
        private string $xuid,
        private string $name,
        private float $health,
        private Location $location,
        private ?LogoutMob $disconnectedMob = null
    ) {
        $this->spawn();
    }

    public function spawn(): void
    {
        $this->disconnectedMob = new LogoutMob($this->getLocation());
        $this->disconnectedMob->setCanSaveWithChunk(true);
        $this->disconnectedMob->setPlayer($this->player);
        $this->disconnectedMob->setInventory($this->player->getInventory()->getContents());
        $this->disconnectedMob->setInventoryArmor($this->player->getArmorInventory()->getContents());
        $this->disconnectedMob->setHealth($this->getHealth());
        $this->disconnectedMob->setNameTagVisible();
        $this->disconnectedMob->setNameTagAlwaysVisible(true);
        $this->disconnectedMob->setNameTag(TextFormat::colorize('&7(Combat-Logger)&c ' . $this->getName()));
        $this->disconnectedMob->spawnToAll();
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return HCFLoader::getInstance()->getSessionManager()->getSession($this->xuid);
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->xuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getHealth(): float
    {
        return $this->health;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }


    /**
     * @return LogoutMob|null
     */
    public function getDisconnectedMob(): ?LogoutMob
    {
        return $this->disconnectedMob;
    }

    /**
     * @param Player $player
     */
    public function join(PlayerPlayer $player): void
    {
        $mob = $this->getDisconnectedMob();

        if ($mob !== null && !$mob->isClosed()) {
            $player->teleport($mob->getLocation());
            $player->setHealth($mob->getHealth());

            $mob->flagForDespawn();
        } else {
            $player->teleport($this->getLocation());
        }
        HCFLoader::getInstance()->getDisconnectedManager()->removeDisconnected((string)$player->getUniqueId());
    }
}