<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class NearCommand extends Command
{
    public function __construct()
    {
        parent::__construct("near", "§hCheck nearby players within 100 blocks (e.g. /near, /NEAR)");
        $this->setPermission("near.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;

        $players = array_filter($sender->getServer()->getOnlinePlayers(), function ($player) use ($sender): bool {
            return $player instanceof Player && $player->getId() !== $sender->getId() && $player->getPosition()->distance($sender->getPosition()) <= 100;
        });

        if (empty($players)) {
            $sender->sendMessage(HCFLoader::$prefix . "§7No players nearby within 100 blocks.");
            return;
        }

        $sender->sendMessage("§d× Near Players ×");

        foreach ($players as $player) {
            $distance = intval($sender->getPosition()->distance($player->getPosition()));
            $sender->sendMessage("§f" . $player->getSession()->getName() . " §7(" . $distance . "m)");
        }
    }
}