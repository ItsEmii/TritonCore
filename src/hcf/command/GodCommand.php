<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class GodCommand extends Command
{
    public function __construct()
    {
        parent::__construct('god', '§hUsa este comando para activar/desactivar el modo dios');
        $this->setPermission('god.command');
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }
        
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }
        
        if ($sender->isGod()) {
            $sender->setGod(false);
            $sender->sendMessage($prefix . TextFormat::RED . "Has desactivado el modo dios");
        } else {
            $sender->setGod(true);
            $sender->sendMessage($prefix . TextFormat::GREEN . "Has activado el modo dios");
        }
    }
}