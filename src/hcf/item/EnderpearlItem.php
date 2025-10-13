<?php

declare(strict_types=1);

namespace hcf\item;

use hcf\entity\EnderpearlEntity;
use JetBrains\PhpStorm\Pure;
use hcf\HCFLoader;
use hcf\player\Player as HCFPlayer;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\EnderPearl as PMEnderPearl;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class EnderpearlItem extends PMEnderPearl
{
    public function __construct()
    {
        parent::__construct(new ItemIdentifier(ItemTypeIds::ENDER_PEARL, 0), 'Ender Pearl');
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EnderpearlEntity($location, $thrower);
    }

    public function getThrowForce(): float
    {
        return 2.0;
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        if ($player instanceof HCFPlayer) {
            $session = $player->getSession();

            if ($player->getCurrentClaim() === '§5Citadel§c'){
                $player->sendMessage(TextFormat::colorize('&cNo puedes usar esto en la zona &5Citadel&c.'));
                return ItemUseResult::FAIL();
            }

            if ($session->getCooldown('enderpearl') !== null) {
                $player->sendMessage(TextFormat::colorize('&cTienes un cooldown de Enderpearl.'));
                return ItemUseResult::FAIL();
            }

            $result = parent::onClickAir($player, $directionVector, $returnedItems);

            if ($result)
                $session->addCooldown('enderpearl', ' &5Enderpearl&r&7: &r&c', 15);

            HCFLoader::$enderPearl["lastUse"][$player->getName()] = $player->getPosition();

            HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->isOnline()) {
                    HCFLoader::$enderPearl["lastUse"][$player->getName()] = "";
                }
            }), 20 * 20);

            return $result;
        }

        return parent::onClickAir($player, $directionVector, $returnedItems);
    }

    #[Pure]
    public static function getLastHit(Player $player){
        if(isset(HCFLoader::$enderPearl["lastUse"][$player->getName()])){
            return HCFLoader::$enderPearl["lastUse"][$player->getName()];
        }
        return false;
    }
}