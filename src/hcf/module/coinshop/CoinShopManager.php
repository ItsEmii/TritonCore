<?php

declare(strict_types=1);

namespace hcf\module\coinshop;

use hcf\HCFLoader;
use hcf\module\coinshop\command\CoinShopCommand;
use hcf\module\coinshop\entity\CoinShopEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * Class CoinShop
 * @package hcf\coinshop
 */
class CoinShopManager
{
    
    public function __construct(){
        EntityFactory::getInstance()->register(CoinShopEntity::class, function (World $world, CompoundTag $nbt): CoinShopEntity {
            return new CoinShopEntity(EntityDataHelper::parseLocation($nbt, $world), CoinShopEntity::parseSkinNBT($nbt), $nbt);
        }, ['CoinShopEntity']);
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('CoinShop', new CoinShopCommand());
    }
    
}