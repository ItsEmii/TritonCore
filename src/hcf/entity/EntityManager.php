<?php

declare(strict_types=1);

namespace hcf\entity;

use hcf\entity\EnderpearlEntity;
use hcf\entity\npcs\SupportEntity;
use hcf\entity\npcs\InfoEntity;
use hcf\entity\npcs\TopFactionsEntity;
use hcf\entity\npcs\TopKDREntity;
use hcf\entity\npcs\TopKillsEntity;
use hcf\entity\projectile\FishingHook;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;
use pocketmine\entity\Entity;

class EntityManager
{
    public function __construct()
    {
        EntityFactory::getInstance()->register(EnderpearlEntity::class, function (World $world, CompoundTag $nbt): EnderpearlEntity {
            return new EnderpearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(InfoEntity::class, function (World $world, CompoundTag $nbt): InfoEntity {
            return new InfoEntity(EntityDataHelper::parseLocation($nbt, $world), InfoEntity::parseSkinNBT($nbt), $nbt);
        }, ['InfoEntity']);

        EntityFactory::getInstance()->register(TopKillsEntity::class, function (World $world, CompoundTag $nbt): TopKillsEntity {
            return new TopKillsEntity(EntityDataHelper::parseLocation($nbt, $world), TopKillsEntity::parseSkinNBT($nbt), $nbt);
        }, ['TopKillsEntity']);

        EntityFactory::getInstance()->register(TopKDREntity::class, function (World $world, CompoundTag $nbt): TopKDREntity {
            return new TopKDREntity(EntityDataHelper::parseLocation($nbt, $world), TopKDREntity::parseSkinNBT($nbt), $nbt);
        }, ['TopKDREntity']);

        EntityFactory::getInstance()->register(TopFactionsEntity::class, function (World $world, CompoundTag $nbt): TopFactionsEntity {
            return new TopFactionsEntity(EntityDataHelper::parseLocation($nbt, $world), TopFactionsEntity::parseSkinNBT($nbt), $nbt);
        }, ['TopFactionsEntity']);

    }
}