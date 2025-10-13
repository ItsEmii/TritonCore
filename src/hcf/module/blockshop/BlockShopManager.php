<?php

declare(strict_types=1);

namespace hcf\module\blockshop;

use hcf\HCFLoader;
use hcf\module\blockshop\command\BlockShopCommand;
use hcf\module\blockshop\entity\BlockShopEntity;
use hcf\module\blockshop\entity\SellShopEntity;
use hcf\module\blockshop\entity\ShopAndSellEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * Class BlockShop
 * @package hcf\blockshop
 */
class BlockShopManager
{
    
    public function __construct(){
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new BlockShopCommand());
        EntityFactory::getInstance()->register(ShopAndSellEntity::class, function (World $world, CompoundTag $nbt): ShopAndSellEntity {
            return new ShopAndSellEntity(EntityDataHelper::parseLocation($nbt, $world), ShopAndSellEntity::parseSkinNBT($nbt), $nbt);
        }, ['ShopAndSellEntity']);
    }
    
}