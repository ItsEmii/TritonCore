<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\HCFClass;
use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class Rogue extends HCFClass
{
    public function __construct()
    {
        parent::__construct(self::ROGUE);
    }

    public function getArmorItems(): array
    {
        return [
            VanillaItems::CHAINMAIL_HELMET(),
            VanillaItems::CHAINMAIL_CHESTPLATE(),
            VanillaItems::CHAINMAIL_LEGGINGS(),
            VanillaItems::CHAINMAIL_BOOTS()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 20 * 15, 2),
            new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 15, 0),
            new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 15, 3)
        ];
    }

    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();

            if ($entity instanceof Player && $damager instanceof Player) {
                $damagerClass = $damager->getClass();
                if ($damagerClass === null)
                    return;

                if ($damagerClass->getTypeId() === HCFClass::ROGUE && $damager->getInventory()->getItemInHand()->getTypeId() === VanillaItems::GOLDEN_SWORD()->getTypeId()) {
                    if ($damager->getCurrentClaim() === 'Spawn')
                        return;

                    if ($entity->getCurrentClaim() === 'Spawn')
                        return;

                    $damagerSession = $damager->getSession();
                    $entitySession = $entity->getSession();

                    if ($damagerSession->getCooldown('starting.timer') !== null || $damagerSession->getCooldown('pvp.timer') !== null) {
                        $event->cancel();
                        return;
                    }

                    if ($entitySession->getCooldown('starting.timer') !== null || $entitySession->getCooldown('pvp.timer') !== null) {
                        $event->cancel();
                        return;
                    }

                    if ($damagerSession->getCooldown('rogue.cooldown') !== null) return;
                    $entity->setHealth($entity->getHealth() - 7);

                    $damager->getInventory()->setItemInHand(VanillaItems::AIR());
                    $damager->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 3, 0));
                    $damager->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * 3, 3));
                    $damager->sendMessage(TextFormat::colorize('&eHas usado Backstab en &4&l' . $entity->getName() . ' &r&e. Su vida actual es &c&l' . $entity->getHealth() . ' &r&eHP'));
                    $damagerSession->addCooldown('rogue.cooldown', ' &gRogue Cooldown&r&7: &r&7', 10);
                }
            }
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($player instanceof Player) {
            $playerClass = $player->getClass();
            if ($playerClass === null)
                return;

            if ($playerClass->getTypeId() === HCFClass::ROGUE) {
                $session = $player->getSession();
                if ($session->getCooldown('starting.timer') !== null || $session->getCooldown('pvp.timer') !== null)
                    return;

                if ($player->getCurrentClaim() === 'Spawn')
                    return;

                if ($item->getTypeId() === VanillaItems::SUGAR()->getTypeId()) {
                    if ($session->getCooldown('speed.cooldown') !== null)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 3));
                    $session->addCooldown('speed.cooldown', ' &gSpeed&r&7: &r&7', 30);
                    $player->sendMessage(TextFormat::colorize('&eAcabas de usar &4Speed IV'));

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                } elseif ($item->getTypeId() === VanillaItems::FEATHER()->getTypeId()) {
                    if ($session->getCooldown('jump.cooldown') !== null)
                        return;
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 7));
                    $session->addCooldown('jump.cooldown', ' &gJump Boost&r&7: &r&7', 30);
                    $player->sendMessage(TextFormat::colorize('&eAcabas de usar &4Jump Boost VIII'));

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                }
            }
        }
    }

    public function getName(): string
    {
        return "Rogue";
    }
}