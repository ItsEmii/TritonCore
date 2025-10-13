<?php

namespace hcf\command;

use hcf\timer\types\TimerSotw;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SpawnCommand extends Command
{
    public function __construct()
    {
        parent::__construct("spawn", TextFormat::colorize("Te hace tp a el spawn"), "/spawn",);
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {        
        if (HCFLoader::getInstance()->getTimerManager()->getSotw()->isActive()){
            $sender->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            $sender->sendMessage(TextFormat::colorize("&aHas sido teletransportado a el Spawn"));
        }elseif ($sender->getCurrentClaim() === 'Spawn') {
            $sender->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            $sender->sendMessage(TextFormat::colorize("&aHas sido teletransportado a el Spawn"));
        }else{
            $sender->sendMessage(TextFormat::RED."You can only use this command in spawn or sotw");
        }
    }
}