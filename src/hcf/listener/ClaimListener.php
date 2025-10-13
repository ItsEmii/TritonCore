<?php

declare(strict_types=1);

namespace hcf\listener;

use CortexPE\std\AABBUtils;
use CortexPE\std\PositionUtils;
use CortexPE\std\Vector3Utils;
use hcf\combatwall\FakeBlock;
use hcf\HCFLoader;
use pocketmine\entity\Location;
use hcf\entity\EnderpearlEntity;
use hcf\player\Player;
use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\FenceGate;
use pocketmine\block\EnderChest;
use pocketmine\block\Chest;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Sign;

use pocketmine\entity\projectile\Snowball;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\EnderPearl;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\WorldException;

/**
 * Class ClaimListener
 * @package hcf\claim
 */
class ClaimListener implements Listener
{
    use SingletonTrait;

    /** @var string */
    const DEATHBAN = '&e(&cDeathban&e)';
    /** @var string */
    const NO_DEATHBAN = '&e(&aNon-Deathban&e)';

    public function handleChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $claimManager = HCFLoader::getInstance()->getClaimManager();

        if ($message === "accept") {
            $event->cancel();
            $creator = $claimManager->getCreator($player->getName());
            if ($creator !== null && $creator->isValid()) {
                if ($creator->getType() === 'faction') {
                    $playerFactionName = $player->getSession()->getFaction();
                    if ($playerFactionName === null) {
                        $player->sendTip(TextFormat::colorize('&7- Debes estar en una &ffaction &7para reclamar un territorio.'));
                        return;
                    }

                    $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($playerFactionName);
                    if ($faction === null) {
                        $player->sendTip(TextFormat::colorize('&7- Tu faction no fue encontrada. Intenta &freconectar&7.'));
                        return;
                    }

                    if ($faction->getBalance() < $creator->calculateValue()) {
                        $player->sendTip(TextFormat::colorize('&7- Tu faction no tiene suficiente &fdinero &7para pagar el claim.'));
                        return;
                    }

                    $minX = abs($creator->getMinX());
                    $maxX = abs($creator->getMaxX());
                    $minZ = abs($creator->getMinZ());
                    $maxZ = abs($creator->getMaxZ());

                    if ($minX < 300 || $maxX < 300 || $minZ < 300 || $maxZ < 300) {
                        $player->sendMessage(TextFormat::colorize('&cDebes estar a más de 300 bloques del spawn.'));
                        $claimManager->removeCreator($player->getName());

                        foreach ($player->getInventory()->getContents() as $slot => $i) {
                            if ($i->getNamedTag()->getTag('claim_type')) {
                                $player->getInventory()->clear($slot);
                                break;
                            }
                        }
                        return;
                    }

                    $faction->setBalance($faction->getBalance() - $creator->calculateValue());
                }

                $creator->deleteCorners($player);
                $claimManager->createClaim(
                    $creator->getName(),
                    $creator->getType(),
                    $creator->getMinX(),
                    $creator->getMaxX(),
                    $creator->getMinZ(),
                    $creator->getMaxZ(),
                    $creator->getWorld()
                );
                $player->sendMessage(TextFormat::colorize('&7- ¡Has hecho el &3claim &7de ' . $creator->getName() . '!'));
                $claimManager->removeCreator($player->getName());

                foreach ($player->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $player->getInventory()->clear($slot);
                        break;
                    }
                }
            }
            return;
        }

        if ($message === "cancel") {
            $event->cancel();
            $creator = $claimManager->getCreator($player->getName());
            if ($creator !== null) {
                $creator->deleteCorners($player);
                $claimManager->removeCreator($player->getName());
                $player->sendMessage(TextFormat::colorize('&7- Has cancelado el claim.'));

                foreach ($player->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $player->getInventory()->clear($slot);
                        break;
                    }
                }
            } else {
                $player->sendMessage(TextFormat::colorize('&7- Aún no estás en modo claim.'));
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @throws WorldException
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());

        if ($event->isCancelled())
            return;

        if ($player->isGod())
            return;

        if ($claim === null) {
            if ($block->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 300)
                $event->cancel();
            return;
        }

        if (in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'custom', 'treasureisland'])) {
            $event->cancel();
            $player->sendTip(TextFormat::colorize(''));
            return;
        }

        if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() && $player->getSession()->getFaction() !== $claim->getName()) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

            if ($faction !== null && $faction->getDtr() > 0.00) {
                $event->cancel();
            }
        }
    }

    public function handleUse(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();

        if ($item->getTypeId() === VanillaItems::WATER_BUCKET()->getTypeId() || $item->getTypeId() === VanillaItems::LAVA_BUCKET()->getTypeId()) {
            $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($player->getPosition());
            if ($claim !== null) {
                if (in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'custom'])) {
                    $event->cancel();
                }
            }
        }
    }


    public function handlePlace(BlockPlaceEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $item = $event->getItem();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());


        if ($block->getTypeId() === VanillaBlocks::TNT()->getTypeId()) {
            $event->cancel();
            return;
        }

        if ($event->isCancelled()) return;
        if ($player->isGod()) return;


        if ($item->getNamedTag()->getTag('pp_packages') !== null) return;
        if ($item->getNamedTag()->getTag('mystery_box') !== null) return;
        if ($item->getNamedTag()->getTag('airdrop_item') !== null) return;


        if ($claim === null) {
            if ($block->getPosition()->distance($player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asVector3()) < 300) {
                $event->cancel();
            }
            return;
        }


        if (in_array($claim->getType(), ['spawn', 'road', 'koth', 'citadel', 'custom', 'treasureisland'])) {
            if ($player->getInventory()->getItemInHand()->getTypeId() === VanillaItems::WATER_BUCKET()->getTypeId()) {
                $player->getInventory()->setItemInHand(VanillaItems::AIR());
            }
            $event->cancel();
            return;
        }

        if (!HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() && $player->getSession()->getFaction() !== $claim->getName()) {
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

            if ($faction !== null && $faction->getDtr() > 0.00) {
                $event->cancel();
            }
        }
    }

    /**
     * @param EntityTeleportEvent $event
     */
    public function handleTeleport(EntityTeleportEvent $event): void
    {
        $entity = $event->getEntity();
        $to = $event->getTo();

        if (!$entity instanceof Player)
            return;
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($to);

        if ($claim === null)
            return;

        if ($entity->getSession()->getCooldown('spawn.tag') !== null) {
            if ($claim->getType() == 'spawn') {
                $event->cancel();
                $entity->sendTip(TextFormat::colorize('&7- Tienes Spawn Tag. No puedes teletransportarte a esta ubicación'));
                return;
            }
        } elseif ($entity->getSession()->getCooldown('pvp.timer') !== null) {
            if ($claim->getType() === 'faction' && $entity->getSession()->getFaction() !== $claim->getName()) {
                $event->cancel();
                $entity->sendTip(TextFormat::colorize('&7- Tienes PvP Timer. No puedes teletransportarte a esta ubicación'));
                return;
            }
        }
        $entity->setCurrentClaim($claim->getName());
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function handleDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName()) !== null) {
            if ($item->getNamedTag()->getTag('claim_type'))
                $event->cancel();
        }
    }

    public function handleFall(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
            if ($player instanceof Player)
                if ($player->getCurrentClaim() === 'Spawn') {
                    $event->cancel();
                }
        }
    }

    public function ItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($player instanceof Player)
            if ($player->getCurrentClaim() === 'Spawn') {

                if ($item instanceof EnderPearl) {
                    $event->cancel();
                }

                if ($item instanceof Snowball) {
                    $event->cancel();
                }
            }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $action = $event->getAction();
        $block = $event->getBlock();
        /** @var Player $player */
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());
        if ($claim !== null) {
            $tile = $player->getWorld()->getTile($block->getPosition());
            if ($tile instanceof Sign) return;
            if ($claim->getName() === "Bank" && ($block instanceof Chest || $block instanceof EnderChest)) return;
            if ($claim->getName() === "TreasureIsland" && ($block instanceof Chest || $block instanceof EnderChest)) return;
        }

        if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName())) !== null) {
            if ($item->getNamedTag()->getTag('claim_type') !== null) {
                $event->cancel();

                if (($claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition())) !== null && ($claim->getType() !== 'koth' || $claim->getName() !== $creator->getName())) {
                    $player->sendTip(TextFormat::colorize('&7- No puedes hacer un claim en un área que ya está &fclaimeada'));
                    return;
                }

                if ($creator->getType() === 'faction') {
                    $pos = $block->getPosition();
                    $distance = sqrt(pow($pos->getX(), 2) + pow($pos->getZ(), 2));

                    if ($distance < 300) {
                        $player->sendMessage(TextFormat::colorize('&cDebes estar a más de 300 bloques del spawn.'));
                        return;
                    }
                }

                if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if ($creator->getFirst() === null) {
                        $creator->calculate($block->getPosition(), $player);
                        $player->sendMessage(TextFormat::colorize('&7[&6!&7] &7Has seleccionado con éxito la &3primera &7posición.'));
                    }
                } elseif ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                    if ($creator->getFirst() !== null) {
                        $result = $creator->calculate($block->getPosition(), $player, false);

                        if (!$result) {
                            $player->sendMessage(TextFormat::colorize('&7- La posición no fue seleccionada en el mismo mundo'));
                            HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());

                            foreach ($player->getInventory()->getContents() as $slot => $i) {
                                if ($i->getNamedTag()->getTag('claim_type')) {
                                    $player->getInventory()->clear($slot);
                                    break;
                                }
                            }
                            return;
                        }

                        if ($creator->calculateClaim($creator->getFirst(), $block->getPosition())) {
                            if ($creator->getType() === 'capzone') return;
                            $player->sendMessage(TextFormat::colorize('&7- La posición fue seleccionada en otra facción'));
                            return;
                        }

                        $player->sendMessage(TextFormat::colorize('&7[&6!&7] &7Has seleccionado con éxito la &3segunda &7posición.'));
                        if ($creator->getType() === 'faction') {
                            $player->sendMessage(TextFormat::colorize('&3» &7El precio de tu claim es &f$' . $creator->calculateValue() . '. &7(Escribe de nuevo /f claim para aceptar o /f claim cancel para cancelar)'));
                        }

                        foreach ($player->getInventory()->getContents() as $slot => $i) {
                            if ($i->getNamedTag()->getTag('claim_type')) {
                                $player->getInventory()->clear($slot);
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($item->equals(VanillaItems::ENDER_PEARL())) {
            if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $block instanceof FenceGate) {
                $session = $player->getSession();
                if ($session->getCooldown('enderpearl') !== null) return;

                $projectile = new EnderpearlEntity(
                    Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->yaw, $player->getLocation()->pitch),
                    $player
                );
                $projectile->setMotion($player->getDirectionVector()->multiply($item->getThrowForce()));
                $projectile->spawnToAll();
                HCFLoader::$enderPearl["lastUse"][$player->getName()] = $player->getPosition();

                $item->pop();
                $session->addCooldown('enderpearl', ' §5Enderpearl&r&7: &r&7', 15);
            }
        }

        if ($player->isGod()) return;
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($block->getPosition());
        if ($claim === null) return;

        if (!$block instanceof Sign) {
            if (
                !HCFLoader::getInstance()->getTimerManager()->getEotw()->isActive() &&
                $player->getSession()->getFaction() !== $claim->getName() &&
                $claim->getType() !== 'spawn'
            ) {
                $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim->getName());

                if (
                    $faction !== null &&
                    $faction->getDtr() > 0.00 &&
                    !HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()
                ) {
                    $event->cancel();

                    if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $block instanceof FenceGate) {
                        if (HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()) return;
                        $distance = $player->getPosition()->distance($block->getPosition());

                        if ($distance <= 3 && !$block->isOpen()) {
                            $player->setMotion($player->getDirectionVector()->multiply(-1.5));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($player->getPosition());

        if ($claim !== null)
            $player->setCurrentClaim($claim->getName());
    }

    public function load(): void
    {
        foreach (HCFLoader::getInstance()->getClaimManager()->getClaims() as $name => $claim) {
        }
    }


    public function onInitialize(): void
    {
        SimplePacketHandler::createInterceptor(HCFLoader::getInstance(), EventPriority::LOWEST)
            ->interceptIncoming(function (InventoryTransactionPacket $pk, NetworkSession $src): bool {
                if (!$pk->trData instanceof UseItemTransactionData) return true;
                $p = $src->getPlayer();
                if (!$this->isVisibleTo($p)) return true;
                return !$this->isInsideProhibitedArea($p, Position::fromObject(Vector3Utils::fromBlockPosition($pk->trData->getBlockPosition()), $p->getWorld()));
            })
            ->interceptIncoming(function (PlayerActionPacket $pk, NetworkSession $src): bool {
                if ($pk->action === PlayerAction::ABORT_BREAK) {
                    $p = $src->getPlayer();
                    if (!$this->isVisibleTo($p)) return true;
                    $this->displayWallBarrier($p);
                    return false;
                }
                if ($pk->action === PlayerAction::START_BREAK) {
                    $p = $src->getPlayer();
                    if (!$this->isVisibleTo($p)) return true;
                    return !$this->isInsideProhibitedArea($p, Position::fromObject(Vector3Utils::fromBlockPosition($pk->blockPosition), $p->getWorld()));
                }
                return true;
            });
    }

    public function onQuit(PlayerQuitEvent $ev): void
    {
    }


    /**
     * @param PlayerMoveEvent $event
     * @throws WorldException
     */
    public function handleMove(PlayerMoveEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getPlayer();
        $claim = HCFLoader::getInstance()->getClaimManager()->insideClaim($player->getPosition());

        $leaving = self::DEATHBAN;
        $entering = self::DEATHBAN;

        if ($event->isCancelled())
            return;
        if (!$this->isBorderLimit($player->getPosition())) {
            $player->teleport($this->correctPosition($player->getPosition()));
        }

        if ($claim === null) {
            if ($player->getCurrentClaim() !== null) {
                $currentClaim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());
                $leavingName = '&c' . $player->getCurrentClaim();

                if ($currentClaim !== null) {
                    if ($currentClaim->getType() === 'spawn') {
                        $leaving = self::NO_DEATHBAN;
                        $leavingName = '&a' . $player->getCurrentClaim();

                        if ($player->getSession()->getCooldown('pvp.timer') !== null && $player->getSession()->getCooldown('pvp.timer')->isPaused())
                            $player->getSession()->getCooldown('pvp.timer')->setPaused(false);
                    } elseif ($currentClaim->getType() === 'road') {
                        $leavingName = '&6' . $player->getCurrentClaim();
                    } elseif ($currentClaim->getType() === 'koth') {
                        $leavingName = '&9KoTH ' . $player->getCurrentClaim();
                    }
                }
                $player->sendTip(TextFormat::colorize('&eAhora saliendo: ' . $leavingName . ' ' . $leaving));
                $player->sendTip(TextFormat::colorize('&aAhora entrando:&c ' . ($player->getPosition()->distance($player->getWorld()->getSafeSpawn()) > 300 ? 'Wilderness' : 'Warzone') . ' ' . $entering));

                $player->setCurrentClaim();
            }
            return;
        }

        if ($player->getCurrentClaim() !== null && $claim->getName() === $player->getCurrentClaim())
            return;

        if ($player->getCurrentClaim() !== null) {
            $currentClaim = HCFLoader::getInstance()->getClaimManager()->getClaim($player->getCurrentClaim());

            if ($currentClaim !== null) {
                $leaving = self::NO_DEATHBAN;
                $leavingName = '&a' . $player->getCurrentClaim();

                if ($currentClaim->getType() === 'spawn') {
                    if ($player->getSession()->getCooldown('pvp.timer') !== null && $player->getSession()->getCooldown('pvp.timer')->isPaused())
                        $player->getSession()->getCooldown('pvp.timer')->setPaused(false);
                } elseif ($currentClaim->getType() === 'road') {
                    $leavingName = '&6' . $player->getCurrentClaim();
                } elseif ($currentClaim->getType() === 'koth') {
                    $leavingName = '&9KoTH ' . $player->getCurrentClaim();
                }
                $player->sendTip(TextFormat::colorize('&eAhora saliendo: ' . $leavingName . ' ' . $leaving));
            }
        }
        $enteringName = '&c' . $claim->getName();

        if ($claim->getType() === 'spawn') {
            $entering = self::NO_DEATHBAN;
            $enteringName = '&a' . $claim->getName();

            if ($player->getSession()->getCooldown('spawn.tag') !== null) {
                $event->cancel();
                return;
            }

            if ($player->getSession()->getCooldown('pvp.timer') !== null && !$player->getSession()->getCooldown('pvp.timer')->isPaused())
                $player->getSession()->getCooldown('pvp.timer')->setPaused(true);
        } elseif ($claim->getType() === 'road') {
            $enteringName = '&6' . $claim->getName();
        } elseif ($claim->getType() === 'koth') {
            $enteringName = '&9KoTH ' . $claim->getName();
        } else {
            if ($player->getSession()->getCooldown('pvp.timer') !== null) {
                $event->cancel();
                return;
            }

            if ($player->getSession()->getCooldown('pvp.timer') !== null && $player->getSession()->getFaction() !== $claim->getName()) {
                $event->cancel();
                return;
            }
        }
        $player->sendTip(TextFormat::colorize('&eAhora entrando: ' . $enteringName . ' ' . $entering));
        $player->sendTip(TextFormat::colorize('&eAhora saliendo:&c ' . ($player->getPosition()->distance($player->getWorld()->getSafeSpawn()) > 300 ? 'Wilderness' : 'Warzone') . ' ' . $entering));
        $player->setCurrentClaim($claim->getName());

        $p = $event->getPlayer();
    }

    protected function isBorderLimit(Vector3 $position): bool
    {
        $border = 1000;
        return $position->getFloorX() >= -$border && $position->getFloorX() <= $border && $position->getFloorZ() >= -$border && $position->getFloorZ() <= $border;
    }

    protected function correctPosition(Vector3 $position): Vector3
    {
        $border = 1000;

        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();

        $xMin = -$border;
        $xMax = $border;

        $zMin = -$border;
        $zMax = $border;

        if ($x <= $xMin) {
            $x = $xMin + 4;
        } elseif ($x >= $xMax) {
            $x = $xMax - 4;
        }
        if ($z <= $zMin) {
            $z = $zMin + 4;
        } elseif ($z >= $zMax) {
            $z = $zMax - 4;
        }
        $y = 72;
        return new Vector3($x, $y, $z);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName()) !== null) {
            HCFLoader::getInstance()->getClaimManager()->removeCreator($player->getName());

            foreach ($player->getInventory()->getContents() as $slot => $i) {
                if ($i->getNamedTag()->getTag('claim_type')) {
                    $player->getInventory()->clear($slot);
                    break;
                }
            }
        }
    }
}