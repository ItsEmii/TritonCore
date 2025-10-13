<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetCoinsCommand extends Command
{

    public function __construct()
    {
        parent::__construct('setcoins', '§hUsa este comando para establecer los coins de un jugador');
        $this->setPermission("op.cmd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player) {
            return;
        }
        
        if (count($args) < 2) {
            $sender->sendMessage($prefix . TextFormat::RED . "Uso: /setcoins {jugador} {coins}");
            return;
        }
        
        $targetName = $args[0];
        $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
        
        if (!$targetPlayer instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Jugador §e{$targetName} §cno encontrado.");
            return;  
        }
        
        $coins = (int)$args[1];
        $targetPlayer->getSession()->setCrystals($coins);
        
        $sender->sendMessage($prefix . TextFormat::YELLOW . "Los coins de §f{$targetName} §fhan sido establecidos en §a$" . $targetPlayer->getSession()->getCrystals());
    }
}