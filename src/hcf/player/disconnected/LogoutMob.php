<?php

declare(strict_types=1);

namespace hcf\player\disconnected;

use hcf\player\Player;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\cooldown\Cooldown;
use hcf\HCFLoader;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\Villager;
use hcf\utils\time\Timer;
use hcf\timer\types\TimerSotw;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LogoutMob extends Villager{

    private static ?Player $player = null;
    private static $inventory;
    private static $armorinventory;
    private ?Player $lastHit = null;
    private int $time = 15;

    public static function getNetworkTypeId() : string
    {
        return EntityIds::VILLAGER;
    }

    public function getName() : string{
        return "Villager";
    }

    protected function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);
    }

    public static function setPlayer(Player $players) {
        self::$player = $players;
    }

    public static function setInventory(array $items) {
        self::$inventory = $items;
    }

    public static function setInventoryArmor(array $items) {
        self::$armorinventory = $items;
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        return array_merge(self::$inventory, self::$armorinventory);
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::onUpdate($currentTick);
        $disconnected = self::$player;

        if ($hasUpdate && $currentTick % 20 === 0 && $disconnected !== null) {
            $this->time--;
            $this->setNameTag(TextFormat::colorize('&7(Combat-Logger)&c ' . $disconnected->getName() . ' &7- &7' . Timer::getTimeToString($this->time)));

            if ($this->time <= 0) {
                HCFLoader::getInstance()->getDisconnectedManager()->removeDisconnected((string)$disconnected->getUniqueId());
                $this->flagForDespawn();
                return true;
            }
        } elseif ($disconnected === null && !$this->isFlaggedForDespawn()) {
            $this->flagForDespawn();
            return true;
        }
        return $hasUpdate;
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        $cause = $source->getCause();
        $disconnected = self::$player;

        if ($disconnected !== null) {
            $session = $disconnected->getSession();
            if ($session !== null) {

                $sotwTimer = HCFLoader::getInstance()->getTimerManager()->getSotw();
                if ($sotwTimer instanceof TimerSotw && $sotwTimer->isActive()) {
                    $source->cancel();
                    if ($source instanceof EntityDamageByEntityEvent) {
                        $damager = $source->getDamager();
                        if ($damager instanceof Player) {
                            $damager->sendMessage(TextFormat::RED . "§cNo puedes atacar a" . $disconnected->getName() . "§cdurante el sotw");
                        }
                    }
                    return;
                }

                if ($session->getCooldown('starting.timer') !== null) {
                    $source->cancel();
                    return;
                }

                if ($cause !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
                    $source->cancel();
                    return;
                }

                if ($source instanceof EntityDamageByEntityEvent) {
                    $damager = $source->getDamager();
                    if ($damager instanceof Player) {
                        $damagerSession = $damager->getSession();
                        if ($damagerSession->getCooldown('starting.timer') !== null) {
                            $source->cancel();
                            return;
                        }

                        if ($damager->getName() === $session->getName() || $damager->getCurrentClaim() === 'Spawn' || ($damagerSession->getCooldown('pvp.timer') !== null) || ($session->getFaction() !== null && $damagerSession->getFaction() !== null && $session->getFaction() === $damagerSession->getFaction())) {
                            $source->cancel();
                            return;
                        }

                        $this->lastHit = $damager;
                        $this->time = 15;

                        $session->addCooldown('spawn.tag', ' &hCombat Tag&r&7: &r&c', 30);
                        $damagerSession->addCooldown('spawn.tag', ' &hCombat Tag&r&7: &r&c', 30);
                    }
                }
            }
        }
        parent::attack($source);
    }

    protected function onDeath(): void
    {
        parent::onDeath();
        $disconnected = self::$player;

        if ($disconnected === null)
            return;
        $session = $disconnected->getSession();
        $killerXuid = null;
        $killer = null;
        $itemInHand = null;
        $message = '';
        $damager = $this->lastHit;

        if ($damager instanceof Player) {
            $killerXuid = (string)$damager->getUniqueId();
            $killer = $damager->getName();
            $itemInHand = $damager->getInventory()->getItemInHand();
            $damagerSession = $damager->getSession();

            $damagerSession->addKill();
            $damagerSession->addKillStreak();

            if ($damagerSession->getKillStreak() > $damagerSession->getHighestKillStreak())
                $damagerSession->addHighestKillStreak();

            if ($damagerSession->getFaction() !== null) {
                $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($damagerSession->getFaction());
                $faction->setPoints($faction->getPoints() + 1);
            }
        }
        $session->setMobKilled(true);
        $session->removeCooldown('spawn.tag');
        $session->addDeath();
        $session->setKillStreak(0);
        $session->addCooldown('pvp.timer', ' &aPvP Timer&r&7: &r&c', 3600, true);

        if ($session->getFaction() !== null) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($session->getFaction());

            $faction->setPoints($faction->getPoints() - 1);
            $faction->setDtr($faction->getDtr() - 1.0);
            $faction->announce(TextFormat::colorize('&cMember Death: &f' . $session->getName() . "\n" . '&cDTR: &f' . $faction->getDtr()));

            # Faction Raid
            if ($faction->getDtr() < 0.00 && !$faction->isRaidable()) {
                $faction->setRaidable(true);
                $faction->setPoints($faction->getPoints() - 10);

                if ($killerXuid !== null) {
                    $killerSession = HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid);

                    if ($killerSession !== null && $killerSession->getFaction()) {
                        $fac = HCFLoader::getInstance()->getFactionManager()->getFaction($killerSession->getFaction());

                        if ($fac !== null) {
                            $fac->setPoints($fac->getPoints() + 3);
                            $fac->announce(TextFormat::colorize('&cThe faction &l' . $faction->getName() . ' &r&cesta raid!'));
                        }
                    }
                }
            }

            # Regen time
            if (!$faction->isRaidable()) {
                $faction->setTimeRegeneration(900);
            } else {
                $regenTime = $faction->getTimeRegeneration();
                $value = $regenTime + 300;

                $faction->setTimeRegeneration($value < 900 ? $value : 900);
            }


        }

        if ($killer === null) {
            $message = '&c' . $session->getName() . '&4[' . $session->getKills() . '] &ese murio';

        } else {
            if (!$itemInHand->isNull() && $itemInHand instanceof Tool) {
                $message = '&c' . $session->getName() . '&4[' . $session->getKills() . '] &efue asesinado por &c' . $killer . '&4[' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . '] &cusando  ' . $itemInHand->getName();

            } else {
                $message = '&c' . $session->getName() . '&4[' . $session->getKills() . '] &emurio a manos de&c' . $killer . '&4[' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . ']';

            }
        }

        Server::getInstance()->broadcastMessage(TextFormat::colorize($message));
    }
}