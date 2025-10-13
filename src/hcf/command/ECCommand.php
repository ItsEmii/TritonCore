<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player as HCFPlayer;
use hcf\HCFLoader;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ECCommand extends Command
{
    public function __construct()
    {
        parent::__construct('ec', '§hComando para abrir el Ender Chest');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof HCFPlayer) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede usarlo un jugador.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->getInventory()->setContents($sender->getEnderInventory()->getContents());
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $player->getEnderInventory()->setContents($inventory->getContents());
        });

        $menu->send($sender, TextFormat::colorize('&4Ender Chest'));
    }
}