<?php

declare(strict_types=1);

namespace hcf\module\coinshop\command;

use hcf\module\coinshop\entity\CoinShopEntity;
use hcf\player\Player;
use hcf\module\coinshop\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class CoinShopCommand extends Command
{

    /**
     * BlockShopCommand construct.
     */
    public function __construct()
    {
        parent::__construct('coinshop', 'Command for coinshop');
        $this->setPermission('op.cmd');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getCurrentClaim() !== 'Spawn')
            return;

        if (isset($args[0]) && $sender->getServer()->isOp($sender->getName())) {
            if ($args[0] === 'npc') {
                if (isset($args[1])) {
                    if ($args[1] === 'buy') {
                        $entity = new CoinShopEntity($sender->getLocation(), $sender->getSkin(), Utils::createBasicNBT($sender));
                        $entity->spawnToAll();
                        return;
                    }
                    return;
                }
            }
        }
        Utils::openCoinShop($sender);
    }
}
