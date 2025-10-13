<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\cooldown\Cooldown;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class RedeemCommand extends Command
{
    private Config $file;
    private Config $cooldownFile;
    private const COOLDOWN_SECONDS = 10800;

    public function __construct()
    {
        parent::__construct("reedem", "§hReclama recompensas de partners", "/reedem <partner>");
        $this->setPermission("use.player.command");

        $dataFolder = HCFLoader::getInstance()->getDataFolder();

        $this->file = new Config($dataFolder . "others/reedem.json", Config::JSON, [
            "mrxedwin400" => ["format" => "§dMrXEdwin400", "redeems" => 0],
            "phpmyforadmin" => ["format" => "§9phpmyforadmin", "redeems" => 0]
        ]);

        $this->cooldownFile = new Config($dataFolder . "others/reedem_cooldowns.json", Config::JSON, []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . "§cEste comando solo puede ser usado en el juego.");
            return false;
        }

        if (!$sender->hasPermission("use.player.command")) {
            $sender->sendMessage($prefix . "§cNo tienes permiso para usar este comando.");
            return false;
        }

        $data = $this->file->getAll();

        if (count($args) !== 1) {
            $sender->sendMessage($prefix . "§eUso: /reedem <partner>");
            $sender->sendMessage("§7Partners disponibles:");
            foreach (array_keys($data) as $partner) {
                $sender->sendMessage("§8- §a" . $partner);
            }
            return false;
        }

        $partnerName = strtolower($args[0]);

        if (!isset($data[$partnerName])) {
            $sender->sendMessage($prefix . "§cEl partner '$partnerName' no existe.");
            return false;
        }

        $playerName = $sender->getName();
        $now = time();

        $lastRedeem = $this->cooldownFile->get($playerName, 0);

        if (($now - $lastRedeem) < self::COOLDOWN_SECONDS) {
            $remaining = self::COOLDOWN_SECONDS - ($now - $lastRedeem);
            $sender->sendMessage($prefix . "§cDebes esperar §e" . Cooldown::format($remaining) . "§c antes de reclamar otra vez.");
            return false;
        }

        Server::getInstance()->dispatchCommand(
                        new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), 
                        "key give Partner 5 \"" . $playerName . "\""
                    );
        Server::getInstance()->dispatchCommand(
                        new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), 
                        "pkg give ". $playerName .  " 5"
                    );

        $data[$partnerName]["redeems"] = ($data[$partnerName]["redeems"] ?? 0) + 1;

        $this->file->setAll($data);
        $this->file->save();

        $this->cooldownFile->set($playerName, $now);
        $this->cooldownFile->save();

        $sender->sendMessage($prefix . "§aHas reclamado §e5 Partner Keys y 5 PKGS §apor el partner §f'$partnerName'§a.");
        return true;
    }
}