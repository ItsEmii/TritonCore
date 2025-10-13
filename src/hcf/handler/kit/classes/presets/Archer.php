<?php

declare(strict_types=1);

namespace hcf\handler\kit\classes\presets;

use hcf\handler\kit\classes\HCFClass;
use hcf\player\Player;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class Archer extends HCFClass
{
    public array $archerMark = [];

    public function __construct()
    {
        parent::__construct(self::ARCHER);
    }

    public function getArmorItems(): array
    {
        return [
            VanillaItems::LEATHER_CAP(),
            VanillaItems::LEATHER_TUNIC(),
            VanillaItems::LEATHER_PANTS(),
            VanillaItems::LEATHER_BOOTS()
        ];
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(VanillaEffects::SPEED(), 20 * 15, 2),
            new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 15, 1),
            new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 20 * 15, 0)
        ];
    }

    public function handleDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if ($event->isCancelled()) return;

        if ($player instanceof Player) {
            if (isset($this->archerMark[$player->getName()]) && $this->archerMark[$player->getName()] > time()) {
                $event->setBaseDamage($event->getBaseDamage() + 0.35);
            }
        }
    }

    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void
    {
        $child = $event->getChild();
        $damager = $event->getDamager();
        $entity = $event->getEntity();

        if ($entity instanceof Player && $damager instanceof Player) {
            $damagerClass = $damager->getClass();
            $entityClass = $entity->getClass();

            if ($damagerClass === null) return;
            if ($damagerClass->getTypeId() !== HCFClass::ARCHER) return;
            if (!$child instanceof Arrow) return;

            if ($entityClass !== null && $entityClass->getTypeId() === HCFClass::ARCHER) {
                $damager->sendMessage("§cNo puedes marcar a otro jugador con clase Archer.");
                return;
            }

            if ($damager->getSession()->getCooldown('starting.timer') !== null || $damager->getSession()->getCooldown('pvp.timer') !== null) return;
            if ($damager->getCurrentClaim() === 'Spawn') return;
            if ($damager->getSession()->getFaction() === $entity->getSession()->getFaction()) return;

            $this->archerMark[$entity->getName()] = time() + 10;

            $distance = intval($entity->getPosition()->distance($damager->getPosition()));
            $damager->sendMessage("§e[§9Distancia de Archer §e(§c{$distance}m§e)] §6Has marcado al jugador por 10 segundos.");
            $entity->sendMessage("§c§l¡Marcado! §r§eUn archer te ha disparado y recibirás 35% más de daño por 10 segundos.");

            $entity->getSession()->addCooldown('archer.mark', ' §5Archer Mark&r&7: &r&7', 10);
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if (!$player instanceof Player) return;

        $playerClass = $player->getClass();
        if ($playerClass === null || $playerClass->getTypeId() !== HCFClass::ARCHER) return;
        if ($player->getSession()->getCooldown('starting.timer') !== null || $player->getSession()->getCooldown('pvp.timer') !== null) return;
        if ($player->getCurrentClaim() === 'Spawn') return;

        if ($item->getTypeId() === VanillaItems::SUGAR()->getTypeId()) {
            if ($player->getSession()->getCooldown('speed.cooldown') !== null) return;
            $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 7, 3));
            $player->getSession()->addCooldown('speed.cooldown', ' §bSpeed&r&7: &r&7', 30);
            $player->sendMessage("§eAcabas de usar §4Speed IV");

            $item->pop();
            $player->getInventory()->setItemInHand($item);

        } elseif ($item->getTypeId() === VanillaItems::FEATHER()->getTypeId()) {
            if ($player->getSession()->getCooldown('jump.cooldown') !== null) return;
            $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 7, 7));
            $player->getSession()->addCooldown('jump.cooldown', ' §bJump Boost&r&7: &r&7', 30);
            $player->sendMessage("§eAcabas de usar §4Jump Boost VIII");

            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }
    }

    public function getName(): string
    {
        return "Archer";
    }
}