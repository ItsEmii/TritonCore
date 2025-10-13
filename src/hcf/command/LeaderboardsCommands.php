<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class LeaderboardsCommands extends Command
{
    public function __construct()
    {
        parent::__construct('leaderboards', '§hUse command for leaderboards');
        $this->setPermission("use.player.command");
    }

    private function getKills(): array
    {
        $kills = [];
        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            $kills[$session->getName()] = $session->getKills();
        }
        return $kills;
    }

    private function getDeaths(): array
    {
        $deaths = [];
        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            $deaths[$session->getName()] = $session->getDeaths();
        }
        return $deaths;
    }

    private function getKDR(): array
    {
        $kdr = [];
        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            if ($session->getDeaths() === 0) {
                $kdr[$session->getName()] = 0.0;
            } else {
                $kdr[$session->getName()] = round($session->getKills() / $session->getDeaths(), 1);
            }
        }
        return $kdr;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
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

        if (!isset($args[0])) {
            $sender->sendMessage($prefix . TextFormat::colorize('&3» &7Usa /leaderboards &f[kills/kdr/deaths]'));
            return;
        }

        $arg = strtolower($args[0]);
        if ($arg === 'kills') {
            $data = $this->getKills();
            arsort($data);
            $sender->sendMessage($prefix . TextFormat::BOLD . TextFormat::DARK_AQUA . "Leaderboard Kills");
            $this->sendTop($sender, $data);
            return;
        }

        if ($arg === 'kdr') {
            $data = $this->getKDR();
            arsort($data);
            $sender->sendMessage($prefix . TextFormat::BOLD . TextFormat::DARK_AQUA . "Leaderboard KDR");
            $this->sendTop($sender, $data);
            return;
        }

        if ($arg === 'deaths') {
            $data = $this->getDeaths();
            arsort($data);
            $sender->sendMessage($prefix . TextFormat::BOLD . TextFormat::DARK_AQUA . "Leaderboard Deaths");
            $this->sendTop($sender, $data);
            return;
        }

        $sender->sendMessage($prefix . TextFormat::RED . "Opción inválida. Usa /leaderboards [kills/kdr/deaths]");
    }

    private function sendTop(Player $sender, array $data): void
    {
        $players = array_keys($data);
        $values = array_values($data);
        for ($i = 0; $i < 10; $i++) {
            if (!isset($players[$i])) break;
            $pos = $i + 1;
            $sender->sendMessage(TextFormat::colorize("&7#{$pos}. &f{$players[$i]} &7- &f{$values[$i]}"));
        }
    }
}