<?php

namespace hcf\abilities\items;

use hcf\abilities\entity\SullCratEntity;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\world\particle\BlockBreakParticle;

class SullCrat implements Listener
{
    public function handleUse(PlayerItemUseEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player instanceof Player) {
            if ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE) {
                if ($item->getNamedTag()->getTag("Abilities") !== null) {
                    if ($item->getNamedTag()->getString("Abilities") === "SullCrat") {
                        $event->cancel();
                        $session = $player->getSession();
                        if ($session->getCooldown('ability.SullCrat') === null) {
                            if ($session->getCooldown('ability.global') === null) {
                                if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null) {
                                    return;
                                }

                                if ($player->getCurrentClaim() === 'Spawn') {
                                    return;
                                }

                                $entity = new SullCratEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch()), $player);
                                $entity->setMotion($event->getDirectionVector()->multiply(1.5));
                                $entity->spawnToAll();
                                $player->sendMessage("§6Has usado §4SullCrat");
                                $session->addCooldown('ability.SullCrat', ' §4SullCrat&r&7: &c', 300);
                                $session->addCooldown('ability.global', ' &5Partner Item&r&7: &c', 5);

                                $item->pop();
                                $player->getInventory()->setItemInHand($item);
                            }
                        }
                    }
                }
            }
        }
    }

    public function handleProjectileHit(ProjectileHitBlockEvent $event) {
        $block = $event->getBlockHit();
        $entity = $event->getEntity();
        if ($entity instanceof SullCratEntity) {
            
            if ($block->getTypeId() === BlockTypeIds::OAK_FENCE_GATE) {
                
                HCFLoader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
                HCFLoader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->addParticle($block->getPosition(), new BlockBreakParticle(VanillaBlocks::OAK_FENCE_GATE()));
            }
        }
    }
}