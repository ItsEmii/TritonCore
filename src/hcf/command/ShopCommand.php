<?php

namespace hcf\command;

use hcf\HCFLoader;
use hcf\module\blockshop\utils\ShopAndSell;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use hcf\player\Player;
use pocketmine\utils\TextFormat;

class ShopCommand extends Command
{
    public function __construct()
    {
        parent::__construct('shop', '§hAbre el menú de la tienda');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        $prefix = "§e[§bTritonMC§e] ";

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        $this->openShopMenu($sender);
    }

    private function openShopMenu(Player $player): void
    {
        ShopAndSell::Shop($player);
    }
}