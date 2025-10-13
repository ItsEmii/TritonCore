<?php

declare(strict_types=1);

namespace hcf\player;

use hcf\HCFLoader;
use hcf\handler\kit\classes\ClassFactory;
use hcf\handler\kit\classes\HCFClass;
use hcf\player\session\Session;
use hcf\module\enchantment\Enchantment;
use hcf\timer\types\TimerCustom;
use hcf\utils\time\Timer;
use hcf\cooldown\Cooldown;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player as BasePlayer;
use pocketmine\player\PlayerInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use hcf\handler\kit\classes\presets\Miner;

class Player extends BasePlayer {
    private PlayerScoreboard $scoreboard;
    private ?HCFClass $class = null;
    private ?string $currentClaim = null;
    private bool $scoreboardMode = true;
    private bool $god = false;
    private int $lastLine = 0;
    private int|float $lastCheck = -1;

    public function __construct(Server $server, NetworkSession $session, PlayerInfo $thisInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag) {
        parent::__construct($server, $session, $thisInfo, $authenticated, $spawnLocation, $namedtag);
        $this->scoreboard = new PlayerScoreboard($this);
    }

    public function getScoreboard(): PlayerScoreboard { return $this->scoreboard; }
    public function getScoreboardMode(): bool { return $this->scoreboardMode; }
    public function setScoreboardMode($mode = true): void { $this->scoreboardMode = $mode; }

    public function getClass(): ?HCFClass { return $this->class; }
    public function setClass(?HCFClass $class): void { $this->class = $class; }

    public function getCurrentClaim(): ?string { return $this->currentClaim; }
    public function setCurrentClaim(?string $claimName = null): void { $this->currentClaim = $claimName; }

    public function isGod(): bool { return $this->god; }
    public function setGod(bool $value): void { $this->god = $value; }

    public function getSession(): ?Session {
        return HCFLoader::getInstance()->getSessionManager()->getSession((string) $this->getUniqueId());
    }

    public function join(): void {
        $this->scoreboard->init();
        if ($this->getSession()?->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($this->getSession()->getFaction());
            $faction?->announce(TextFormat::colorize('§aFACTION§r §7: §aMiembro en linea: &f' . $this->getSession()->getName()));
        }

        if (($disc = HCFLoader::getInstance()->getDisconnectedManager()->getDisconnected((string) $this->getUniqueId())) !== null)
            $disc->join($this);

        $this->getNetworkSession()->sendDataPacket(GameRulesChangedPacket::create(['showCoordinates' => new BoolGameRule(true, false)]));

        if ($this->getSession()?->isMobKilled()) {
            $this->getSession()->setMobKilled(false);
            $this->getInventory()->clearAll();
            $this->getArmorInventory()->clearAll();
            $this->getEffects()->clear();
            $this->setHealth($this->getMaxHealth());
            $this->teleport($this->getWorld()->getSafeSpawn());
        }

        if ($this->getSession()?->isLogout()) $this->getSession()->setLogout(false);
    }

    public function onUpdate(int $currentTick): bool {
        $update = parent::onUpdate($currentTick);
        if (!$update) return false;

        if ($currentTick % 20 === 0) {
            foreach ($this->getArmorInventory()->getContents() as $armor)
                foreach ($armor->getEnchantments() as $ench)
                    if (($type = $ench->getType()) instanceof Enchantment) $type->giveEffect($this);

            $this->updateScoreboard();
            $this->loadInvisibility();

            $class = $this->getClass();
            if ($class !== null) {
                $class->onRun($this);
                if ($class instanceof Miner) $class->applyInvisibility($this);
            } else {
                foreach (ClassFactory::getClasses() as $class) {
                    if ($class->isActive($this)) {
                        $this->class = $class;
                        break;
                    }
                }
            }
        }

        if ($currentTick % 40 === 0) $this->lastLine = ($this->lastLine >= 2 ? 0 : $this->lastLine + 1);
        return true;
    }

    public function loadInvisibility(): void {
        if (!$this->getEffects()->has(VanillaEffects::INVISIBILITY())) return;
        $meta = clone $this->getNetworkProperties();
        $meta->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
        $pk = new SetActorDataPacket();
        $pk->actorRuntimeId = $this->getId();
        $pk->metadata = $meta->getAll();
        $pk->syncedProperties = new PropertySyncData([], []);

        foreach ($this->getViewers() as $viewer) {
            if ($viewer instanceof self && $viewer->getSession()?->getFaction() === $this->getSession()?->getFaction())
                $viewer->getNetworkSession()->sendDataPacket($pk);
        }
    }

    protected function processMostRecentMovements(): void {
        if (microtime(true) - $this->lastCheck > 1) {
            $this->lastCheck = microtime(true);
            foreach ($this->getArmorInventory()->getContents() as $armor)
                foreach ($armor->getEnchantments() as $ench)
                    if (($type = $ench->getType()) instanceof Enchantment) $type->handleMove($this);
        }
        parent::processMostRecentMovements();
    }

    private function updateScoreboard(): void {
        if (!$this->scoreboardMode) {
            if ($this->scoreboard->isSpawned()) $this->scoreboard->remove();
            return;
        }

        $this->scoreboard->updateTitles();
        $lines = [TextFormat::colorize(HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'))];

        foreach ($this->getSession()?->getCooldowns() ?? [] as $cooldown)
            if ($cooldown->isVisible())
                $lines[] = TextFormat::colorize('§f' . $cooldown->getFormat() . Cooldown::format($cooldown->getTime()));

        foreach ($this->getSession()?->getEnergies() ?? [] as $energy) {
            $lines[] = TextFormat::colorize('');
            $lines[] = TextFormat::colorize(' §7' . $energy->getFormat() . ($energy->getEnergy() . '.0'));
        }

        $claim = $this->getCurrentClaim();
        if ($claim !== null && ($claimObj = HCFLoader::getInstance()->getClaimManager()->getClaim($claim)) !== null) {
            $claimName = match ($claimObj->getType()) {
                'spawn' => '§a' . $claim,
                'road' => '§6' . $claim,
                'koth' => '§4' . $claim,
                default => '§6' . $claim
            };
            $lines[] = TextFormat::colorize(' §6Claim&r&7§r: &7' . $claimName);
            if ($claimObj->getType() === 'spawn') {
                $lines[] = TextFormat::colorize(' &aMoney&r&7: &a$' . $this->getSession()?->getBalance());
                $lines[] = TextFormat::colorize(' &gCoins&r&7: &a$' . $this->getSession()?->getCrystals());
                $lines[] = TextFormat::colorize(" &aPing: &a" . $this->getNetworkSession()->getPing() . "ms");
            }
        } else {
            $lines[] = TextFormat::colorize(' §6Claim&r&7§r: §7' . ($this->getPosition()->distance($this->getWorld()->getSafeSpawn()) > 400 ? '§7Wilderness' : '§cWarzone'));
        }

        foreach ([
            'SOTW' => 'getSotw',
            'x2Points' => 'getPoints',
            'EOTW' => 'getEotw',
            'Purge' => 'getPurge',
            'Keyall' => 'getKeyAll',
            'KeyallOP' => 'getKeyAllOP',
            'PKGALL' => 'getPackages'
        ] as $label => $method) {
            $timer = HCFLoader::getInstance()->getTimerManager()->{$method}();
            if ($timer->isActive())
                $lines[] = TextFormat::colorize(" §" . ($label === 'EOTW' || $label === 'Purge' ? '4' : 'a') . "{$label}§r: §c" . $timer->getFormat() . Timer::getTimeToString($timer->getTime()));
        }

        foreach (HCFLoader::getInstance()->getTimerManager()->getCustomTimers() as $custom)
            if ($custom instanceof TimerCustom && $custom->isActive())
                $lines[] = TextFormat::colorize(' ' . $custom->getFormat() . Timer::getTimeToString($custom->getTime()));

        if (($kothName = HCFLoader::getInstance()->getKothManager()->getKothActive()) !== null && ($koth = HCFLoader::getInstance()->getKothManager()->getKoth($kothName)) !== null) {
            $lines[] = TextFormat::colorize('');
            $lines[] = TextFormat::colorize(' §4' . $koth->getName() . '§c ' . Timer::getTimeToString($koth->getProgress()));
            $lines[] = TextFormat::colorize(' §7' . $koth->getCoords());
        }

        if (($facName = $this->getSession()?->getFaction()) !== null && ($faction = HCFLoader::getInstance()->getFactionManager()->getFaction($facName)) !== null) {
            if (($focus = $faction->getFocus()) !== null && ($targetFac = HCFLoader::getInstance()->getFactionManager()->getFaction($focus)) !== null) {
                if (count($lines) > 1) $lines[] = TextFormat::colorize(HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'));
                $lines[] = " §6" . $targetFac->getName() . "§r§7: §6" . ($targetFac->getHome()?->getFloorX() . ", " . $targetFac->getHome()?->getFloorZ() ?? "no tiene home");
                $lines[] = " §bDTR§r§7: §6" . $targetFac->getDtr() . " §c■ §f(§a" . count($targetFac->getOnlineMembers()) . "§7/§c" . count($targetFac->getMembers()) . "§f)";
            }

            if (($rally = $faction->getRally()) !== null) {
                $lines[] = TextFormat::colorize('');
                $lines[] = TextFormat::colorize(' §fRally&r&7: &5' . $rally[0]);
                $lines[] = TextFormat::colorize('§fXYZ&r&7: §7' . $rally[1]->getFloorX() . ', ' . $rally[1]->getFloorY() . ', ' . $rally[1]->getFloorZ());
            }
        }

        $lines[] = TextFormat::colorize('&5' . HCFLoader::getInstance()->getConfig()->get('scoreboard.placeholder'));
        if (count($lines) <= 2) {
            if ($this->scoreboard->isSpawned()) $this->scoreboard->remove();
            return;
        }

        if (($class = $this->getClass()) !== null) {
            $lines[] = TextFormat::colorize(' §fClass: §6' . $class->getName());
        }

        if (!$this->scoreboard->isSpawned()) $this->scoreboard->init();
        else $this->scoreboard->clear();

        foreach ($lines as $line) $this->scoreboard->addLine($line . ' ');
    }
}