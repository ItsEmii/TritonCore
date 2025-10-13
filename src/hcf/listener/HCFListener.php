<?php

declare(strict_types=1);

namespace hcf\listener;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\HCFLoader;
use hcf\cooldown\Cooldown;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\lang\Translatable;
use pocketmine\player\chat\LegacyRawChatFormatter;
use pocketmine\Server as ServerInsano;
use hcf\entity\EnderpearlEntity;
use hcf\item\Fireworks;
use pocketmine\utils\Config;
use hcf\player\Player;
use pocketmine\block\tile\Sign;
use pocketmine\block\Water;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Armor;
use pocketmine\item\Bucket;
use pocketmine\item\EnderPearl;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Shovel;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ThrowSound;

class HCFListener implements Listener
{
    private const TAG_DURATION = 30;

    public function handleDamage(EntityDamageEvent $event): void
    {
        $cause = $event->getCause();
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            if ($event->isCancelled()) return;

            $session = $entity->getSession();
            if ($session === null) return;

            if ($session->getCooldown('starting.timer')) {
                $event->cancel();
                return;
            }

            if ($session->getCooldown('pvp.timer') !== null && ($cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK || $cause === EntityDamageEvent::CAUSE_PROJECTILE)) {
                $event->cancel();
                return;
            }

            if ($entity->getCurrentClaim() === 'Spawn') {
                $event->cancel();
                return;
            }

            if ($event instanceof EntityDamageByEntityEvent || $event instanceof EntityDamageByChildEntityEvent) {
                $damager = $event->getDamager();

                if ($damager instanceof Player) {
                    $damagerSession = $damager->getSession();
                    if ($damagerSession === null) return;

                    if ($damagerSession->getCooldown('starting.timer') !== null || $damagerSession->getCooldown('pvp.timer') !== null) {
                        $event->cancel();
                        return;
                    }

                    if ($damager->getCurrentClaim() === 'Spawn') {
                        $event->cancel();
                        return;
                    }

                    if ($session->getFaction() !== null && $damagerSession->getFaction() !== null && $session->getFaction() === $damagerSession->getFaction()) {
                        $damager->sendMessage(TextFormat::colorize('&eNo puedes hacer daño  a &2' . $entity->getSession()->getName() . '§e.'));
                        $event->cancel();
                        return;
                    }

                    $session->setLastDamager($damager->getUniqueId()->toString());
                    $session->setLastDamageTime(time());

                    $session->addCooldown('lastDamage', '', 15, false, false);
                    $session->addCooldown('spawn.tag', ' §hCombat Tag&r:§c ', 30);
                    $damagerSession->addCooldown('spawn.tag', ' §hCombat Tag&r:§c ', 30);
                }
            }
        }
    }

    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;

        $session = $player->getSession();
        if ($session === null) return;

        $last = $player->getLastDamageCause();
        $killerXuid = null;
        $killer = null;
        $itemInHand = null;

        $lastDamagerXuid = $session->getLastDamager();
        $lastDamageTime = $session->getLastDamageTime();
        
        if ($lastDamagerXuid !== null && (time() - $lastDamageTime) <= self::TAG_DURATION) {
            $killerSession = HCFLoader::getInstance()->getSessionManager()->getSession($lastDamagerXuid);
            if ($killerSession !== null) {
                $killerXuid = $lastDamagerXuid;
                $killer = $killerSession->getName();
                $killerPlayer = HCFLoader::getInstance()->getServer()->getPlayerByRawUUID($lastDamagerXuid);
                if ($killerPlayer !== null) {
                    $itemInHand = $killerPlayer->getInventory()->getItemInHand();
                }
            }
        }

        if ($killerXuid === null && ($last instanceof EntityDamageByEntityEvent || $last instanceof EntityDamageByChildEntityEvent)) {
            $damager = $last->getDamager();
            if ($damager instanceof Player) {
                $killerXuid = (string)$damager->getUniqueId();
                $killer = $damager->getSession()->getName();
                $itemInHand = $damager->getInventory()->getItemInHand();
            }
        }

        if ($killerXuid !== null) {
            $damagerSession = HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid);
            if ($damagerSession !== null) {
                $damagerSession->addKill();
                $damagerSession->addKillStreak();
                $damagerSession->setCrystals($damagerSession->getCrystals() + 1);

                if (HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()) {
                    $damagerSession->setCrystals($damagerSession->getCrystals() + 1);
                    ServerInsano::getInstance()->dispatchCommand(
                        new ConsoleCommandSender(ServerInsano::getInstance(), ServerInsano::getInstance()->getLanguage()), 
                        "key give Purge 1 \"" . $damagerSession->getName() . "\""
                    );
                }

                if (HCFLoader::getInstance()->getTimerManager()->getPoints()->isActive()) {
                    $damagerSession->setCrystals($damagerSession->getCrystals() + 1);
                }

                if ($damagerSession->getKillStreak() > $damagerSession->getHighestKillStreak()) {
                    $damagerSession->addHighestKillStreak();
                }

                if ($damagerSession->getFaction() !== null) {
                    $factionManager = HCFLoader::getInstance()->getFactionManager();
                    if (($faction = $factionManager->getFaction($damagerSession->getFaction())) !== null) {
                        $faction->setPoints($faction->getPoints() + (HCFLoader::getInstance()->getTimerManager()->getPoints()->isActive() ? 2 : 1));
                    }
                }
            }
        }

        $session->removeCooldown('spawn.tag');
        $spawnClaim = HCFLoader::getInstance()->getClaimManager()->getClaim('Spawn');
        if ($spawnClaim !== null && $spawnClaim->getType() === 'spawn') {
            $player->setCurrentClaim($spawnClaim->getName());
        }
        $session->addDeath();
        $session->setKillStreak(0);
        $session->addCooldown('starting.timer', ' §aPvP Timer§r§c: ', 3600);
        
        $session->setLastDamager('');
        $session->setLastDamageTime(0);

        if ($session->getFaction() !== null) {
            $factionManager = HCFLoader::getInstance()->getFactionManager();
            if (($faction = $factionManager->getFaction($session->getFaction())) !== null) {
                $faction->setPoints($faction->getPoints() - 1);
                $faction->setDtr($faction->getDtr() - 1.0);
                $faction->announce(TextFormat::colorize('§cMiembro Asesinado: &f' . $session->getName() . "\n" . '&cDTR: &f' . $faction->getDtr()));

                if ($faction->getDtr() < 0.00 && !$faction->isRaidable()) {
                    $faction->setRaidable(true);
                    $faction->setPoints($faction->getPoints() - 5);

                    $announcement = TextFormat::colorize('&bThe &gfaction &c&l' . $faction->getName() . ' &r&bHa quedado raideable!!' . "\n" . '&l&7█&f█████&7█' . "\n" . '&l&f███&4█&f███  &cRaid Anuncio' . "\n" . '&l&f███&4█&f███  &r&6[&c' . $faction->getName() . '&6] esta raid ahora' . "\n" . '&l&f████&f███  &r&a[Use /f focus ' . $faction->getName() . ']' . "\n" . '&l&f███&4█&f███' . "\n" . '&l&7█&f█████&7█');
                    ServerInsano::getInstance()->broadcastMessage($announcement);

                    if ($killerXuid !== null) {
                        $killerSession = HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid);
                        if ($killerSession !== null && $killerSession->getFaction()) {
                            if (($fac = $factionManager->getFaction($killerSession->getFaction())) !== null) {
                                $fac->setPoints($fac->getPoints() + 5);
                            }
                        }
                    }
                }

                if (!$faction->isRaidable()) {
                    $faction->setTimeRegeneration(900);
                } else {
                    $faction->setTimeRegeneration(min($faction->getTimeRegeneration() + 300, 900));
                }
            }
        }

        $deathMessage = match ($killer) {
    null => '§c' . $session->getName() . '§6[§e' . $session->getKills() . '§6] §emurio',
    default => ($itemInHand !== null && !$itemInHand->isNull() && $itemInHand instanceof Tool) ?
        '§c' . $session->getName() . '§6[§e' . $session->getKills() . '§6] §efue asesinado a manos de§c' . $killer . '§6[§e' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . '§6] §cusando ' . $itemInHand->getName() :
        '§c' . $session->getName() . '§6[§e' . $session->getKills() . '§6] §emurio a manos de §c' . $killer . '§6[§e' . HCFLoader::getInstance()->getSessionManager()->getSession($killerXuid)->getKills() . '§6]§r',
};
        $event->setDeathMessage(TextFormat::colorize($deathMessage));
    }

    public function handlePickupItem(EntityItemPickupEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            $session = $entity->getSession();
            if ($session === null || ($session->getCooldown('pvp.timer') === null && $session->getCooldown('starting.timer') === null)) return;

            $origin = $event->getOrigin();
            $owningEntity = $origin->getOwningEntity();

            if ($owningEntity === null || $owningEntity->getId() !== $entity->getId()) {
                $entity->sendPopup(TextFormat::colorize("&cNo puedes recolectar objetos con el timer PvP, usa /pvp enable "));
                $event->cancel();
            }
        }
    }

    public function handleCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(Player::class);
    }

    public function handleMove(PlayerMoveEvent $event): void {}

    public function handleExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $session = $player->getSession();
            if ($session === null) return;

            if (($claim = $player->getCurrentClaim()) !== null && $claim === 'Spawn') {
                $event->cancel();
                if ($player->getHungerManager()->getFood() !== $player->getHungerManager()->getMaxFood()) {
                    $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                }
                return;
            }

            if ($session->hasAutoFeed()) {
                $event->cancel();
                if ($player->getHungerManager()->getFood() !== $player->getHungerManager()->getMaxFood()) {
                    $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                }
                return;
            }
        }
    }

    public function handleInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;

        $pos = $player->getPosition();
        $world = $player->getWorld();

        if ($pos->distance($world->getSafeSpawn()) < 170) {
            $item = $player->getInventory()->getItemInHand();
            if ($item instanceof Bucket || $item->getNamedTag()->getTag('pp_packages') !== null || $item->getNamedTag()->getTag('mystery_box') !== null || $item->getNamedTag()->getTag('airdrop') !== null) {
                $event->cancel();
                return;
            }
            $block = $event->getBlock();
            if ($block instanceof Water || $block instanceof Sign || $item instanceof Fireworks || $item instanceof Shovel || $item instanceof Hoe) {
                $event->cancel();
                return;
            }
        }

        if ($player->getInventory()->getItemInHand() instanceof FlintSteel) {
            $event->cancel();
            return;
        }

        if ($event instanceof PlayerBucketEmptyEvent) {
            $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($event->getBlock()->getPosition());
            if ($claim === null && $pos->distance($world->getSafeSpawn()) < 300) {
                $event->cancel();
                return;
            }
            if ($player->getInventory()->getItemInHand()->equals(VanillaItems::WATER_BUCKET(), false, false)) {
                $event->cancel();
                return;
            }
            if ($player->getInventory()->getItemInHand()->equals(VanillaItems::LAVA_BUCKET(), false, false)) {
                $event->cancel();
                return;
            }
            if ($claim !== null && in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'deathzone'], true)) {
                $event->cancel();
                return;
            }
        }
    }

    public function handleItemConsume(PlayerItemConsumeEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($player instanceof Player) {
            if ($event->isCancelled()) return;

            $session = $player->getSession();
            if ($session !== null) {
                if ($item->getTypeId() == ItemTypeIds::GOLDEN_APPLE) {
                    if ($session->getCooldown('apple') !== null) {
                        $event->cancel();
                        return;
                    }
                    $session->addCooldown('apple', ' §eApple: §r§c', 10);
                } elseif ($item->getTypeId() == ItemTypeIds::ENCHANTED_GOLDEN_APPLE) {
                    if ($session->getCooldown('apple.enchanted') !== null) {
                        $event->cancel();
                        return;
                    }
                    $session->addCooldown('apple.enchanted', ' §uGapple§r§7: &r&c', 3600);
                }
            }
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        if ($event->getItem() instanceof Armor) {
            $event->cancel();
        }
    }

    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $player->join();

        $joinMessage = str_replace('{player}', $player->getName(), HCFLoader::getInstance()->getConfig()->get('join.message'));
        $event->setJoinMessage(TextFormat::colorize($joinMessage));
    }

    public function handleLogin(PlayerLoginEvent $event): void
{
    $player = $event->getPlayer();
    $uuid = $player->getUniqueId()->toString();
    $name = $player->getName();

    $sessionManager = HCFLoader::getInstance()->getSessionManager();
    $session = $sessionManager->getSession($uuid);

    if ($session === null) {
        $sessionManager->addSession($uuid, [
            'name' => $name,
            'faction' => null,
            'prefix' => null,
            'balance' => 1000,
            'crystals' => 0,
            'cooldowns' => [],
            'energies' => [],
            'stats' => [
                'kills' => 0,
                'deaths' => 0,
                'killStreak' => 0,
                'highestKillStreak' => 0
            ]
        ]);
    } elseif ($session->getName() !== $name) {
        $session->setName($name);
    }
}

    public function handleQuit(PlayerQuitEvent $event): void
{
    $player = $event->getPlayer();
    $loader = HCFLoader::getInstance();

    $quitMessage = $loader->getConfig()->get('quit.message');
    if ($quitMessage !== "") {
        $event->setQuitMessage(TextFormat::colorize(str_replace('{player}', $player->getName(), $quitMessage)));
    } else {
        $event->setQuitMessage("");
    }

    $session = $player->getSession(); 
    if ($session === null) return;


    $factionName = $session->getFaction();
    if ($factionName !== null) {
        $faction = $loader->getFactionManager()->getFaction($factionName);
        if ($faction !== null) {
            $faction->announce(TextFormat::colorize("&cMember offline: &f" . $session->getName() . "\n&cDTR: &f" . $faction->getDtr()));
        }
    }

    if (!$session->isLogout()) {
        $claim = $loader->getClaimManager()->insideClaim($player->getPosition());
        if ($claim !== null && $claim->getType() !== 'spawn') {
            $loader->getDisconnectedManager()->addDisconnected($player);
        }
    }
}

    public function saveDeathForRollback(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $inventory = $player->getInventory()->getContents();
        $armorInventory = $player->getArmorInventory()->getContents();
        $items = [];
        $nbtSerializer = new BigEndianNbtSerializer();

        foreach (array_merge($inventory, $armorInventory) as $item) {
            try {
                $items[] = base64_encode($nbtSerializer->write(new \pocketmine\nbt\TreeRoot($item->nbtSerialize())));
            } catch (\Throwable $e) {}
        }

        $playerName = $player->getName();
        $data = new Config(HCFLoader::getInstance()->getDataFolder() . "rollbackdata.yml", Config::YAML);
        $deaths = $data->get($playerName, []);
        $currentTime = date("Y-m-d H:i:s");

        $lastDeath = end($deaths);
        if ($lastDeath === false || $lastDeath["time"] !== $currentTime) {
            $deathId = "ID" . (count($deaths) + 1);

            $cause = $event->getDeathMessage();
            if (is_object($cause) && method_exists($cause, "getText")) {
                $cause = $cause->getText();
            }

            $killer = "Unknown";
            $lastDamageCause = $player->getLastDamageCause();
            if ($lastDamageCause instanceof EntityDamageByEntityEvent) {
                $damager = $lastDamageCause->getDamager();
                if ($damager instanceof \pocketmine\player\Player) {
                    $killer = $damager->getName();
                }
            }

            $deaths[$deathId] = [
                "items" => $items,
                "position" => $player->getPosition()->__toString(),
                "cause" => $cause,
                "killer" => $killer,
                "time" => $currentTime
            ];

            $data->set($playerName, $deaths);
            $data->save(false);
        }
    }
}