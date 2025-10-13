<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetBalanceCommand extends Command
{

    public function __construct()
    {
        parent::__construct('setbalance', '§hUsa este comando para ajustar el balance de un jugador');
        $this->setPermission("op.cmd");
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player) {
            return;
        }
        
        if (count($args) < 2) {
            $sender->sendMessage($prefix . TextFormat::RED . "Uso: /setbalance {jugador} {monedas}");
            return;
        }
        
        $targetName = $args[0];
        $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
        
        if (!$targetPlayer instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Jugador §e{$targetName} §cno encontrado.");
            return;  
        }
        
        $coins = (int)$args[1];
        $targetPlayer->getSession()->setBalance($coins);
        
        $sender->sendMessage($prefix . TextFormat::YELLOW . "El balance de §f{$targetName} §fha sido establecido en §a$" . $targetPlayer->getSession()->getBalance());
    }
}