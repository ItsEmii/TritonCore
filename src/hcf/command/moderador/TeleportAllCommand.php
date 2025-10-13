<?php

namespace hcf\command\moderador;

use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class TeleportAllCommand extends Command
{
    public function __construct()
    {
        parent::__construct('tpall', '§hComando para teletransportar a todos hacia ti');
        $this->setPermission('op.cmd');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) return;

        if (!$this->testPermission($sender)) return;

        if (count($args) > 0) {
            $sender->sendMessage($prefix . TextFormat::RED . "Uso correcto: /tpall");
            return;
        }

        $sender->sendMessage($prefix . TextFormat::YELLOW . "Teletransportando a todos hacia ti en 5 segundos...");
        $sender->sendMessage($prefix . TextFormat::GRAY . "Prepárate para teletransportar a todos los jugadores.");

        HCFLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($sender, $prefix): void {
            $server = HCFLoader::getInstance()->getServer();

            foreach ($server->getOnlinePlayers() as $player) {
                $player->teleport($sender->getLocation());
            }

            $sender->sendMessage($prefix . TextFormat::GREEN . "¡Todos los jugadores han sido teletransportados hacia ti!");
        }), 20 * 5);
    }
}