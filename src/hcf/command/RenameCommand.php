<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\utils\TextFormat;

class RenameCommand extends Command
{
    public function __construct()
    {
        parent::__construct('rename', '§hComando para renombrar ítems');
        $this->setPermission('rename.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player)
            return;

        if (!$this->testPermission($sender))
            return;

        if (count($args) < 1) {
            $sender->sendMessage($prefix . "§cUsa: /rename [nombre]");
            return;
        }

        $item = clone $sender->getInventory()->getItemInHand();
        $name = implode(' ', $args);

        if (!$item instanceof Tool && !$item instanceof Armor) {
            $sender->sendMessage($prefix . "§cNo tienes herramienta ni armadura en la mano");
            return;
        }

        $item->setCustomName(TextFormat::colorize($name));
        $sender->getInventory()->setItemInHand($item);
        $sender->sendMessage($prefix . "§aHas renombrado el ítem correctamente");
    }
}