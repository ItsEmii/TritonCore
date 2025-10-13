<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\cooldown\Cooldown;
use itoozh\crates\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class ReclaimCommand extends Command
{
    private array $reclaims = [
        "Legend" => [
            "permission" => "legend.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 55],
                ["crate" => "Items", "amount" => 50],
                ["crate" => "Rare", "amount" => 45],
                ["crate" => "Fall", "amount" => 40],
                ["crate" => "Partner", "amount" => 35],
                ["crate" => "Blessed", "amount" => 30],
                ["crate" => "Angel", "amount" => 25]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §5Legend§a correctamente!"
        ],
        "Angel" => [
            "permission" => "angel.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 50],
                ["crate" => "Items", "amount" => 45],
                ["crate" => "Rare", "amount" => 40],
                ["crate" => "Fall", "amount" => 35],
                ["crate" => "Partner", "amount" => 30],
                ["crate" => "Blessed", "amount" => 25],
                ["crate" => "Angel", "amount" => 20]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §1Angel§a correctamente!"
        ],
        "Blessed" => [
            "permission" => "blessed.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 45],
                ["crate" => "Items", "amount" => 40],
                ["crate" => "Rare", "amount" => 35],
                ["crate" => "Fall", "amount" => 30],
                ["crate" => "Partner", "amount" => 25],
                ["crate" => "Blessed", "amount" => 20],
                ["crate" => "Angel", "amount" => 30]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §uBlessed§a correctamente!"
        ],
        "Faith" => [
            "permission" => "faith.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 40],
                ["crate" => "Items", "amount" => 35],
                ["crate" => "Rare", "amount" => 30],
                ["crate" => "Fall", "amount" => 25],
                ["crate" => "Partner", "amount" => 20],
                ["crate" => "Blessed", "amount" => 15],
                ["crate" => "Blessed", "amount" => 10]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §eFaith§a correctamente!"
        ],
        "Peaceful" => [
            "permission" => "peaceful.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 35],
                ["crate" => "Items", "amount" => 30],
                ["crate" => "Rare", "amount" => 25],
                ["crate" => "Fall", "amount" => 20],
                ["crate" => "Partner", "amount" => 15],
                ["crate" => "Blessed", "amount" => 10],
                ["crate" => "Angel", "amount" => 5]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §2Peaceful§a correctamente!"
        ],
        "Believer" => [
            "permission" => "believer.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 30],
                ["crate" => "Items", "amount" => 25],
                ["crate" => "Rare", "amount" => 20],
                ["crate" => "Fall", "amount" => 15],
                ["crate" => "Partner", "amount" => 10],
                ["crate" => "Blessed", "amount" => 5],
                ["crate" => "Angel", "amount" => 3]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §6Believer§a correctamente!"
        ],
        "Decoy" => [
            "permission" => "decoy.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 20],
                ["crate" => "Items", "amount" => 15],
                ["crate" => "Rare", "amount" => 10],
                ["crate" => "Fall", "amount" => 5],
                ["crate" => "Partner", "amount" => 5],
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §7Decoy§a correctamente!"
        ],
        "Guest" => [
            "permission" => "free.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 25],
                ["crate" => "Items", "amount" => 20],
                ["crate" => "Rare", "amount" => 15],
                ["crate" => "Fall", "amount" => 10],
                ["crate" => "Partner", "amount" => 5],
                ["crate" => "Blessed", "amount" => 5]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §aGuest§a correctamente!"
        ],
        "Media" => [
            "permission" => "media.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 50],
                ["crate" => "Items", "amount" => 45],
                ["crate" => "Rare", "amount" => 35],
                ["crate" => "Fall", "amount" => 30],
                ["crate" => "Partner", "amount" => 25],
                ["crate" => "Blessed", "amount" => 20]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §5Media§a correctamente!"
        ],
        "Staff" => [
            "permission" => "staff.reclaim",
            "rewards" => [
                ["crate" => "Starter", "amount" => 50],
                ["crate" => "Items", "amount" => 45],
                ["crate" => "Rare", "amount" => 35],
                ["crate" => "Fall", "amount" => 30],
                ["crate" => "Partner", "amount" => 25],
                ["crate" => "Blessed", "amount" => 20],
                ["crate" => "Angel", "amount" => 15]
            ],
            "message" => "§a¡Has reclamado tus recompensas del rango §hStaff§a correctamente!"
        ]
    ];

    private const GLOBAL_COOLDOWN = 86400;

    public function __construct()
    {
        parent::__construct("reclaim", "§hUsa este comando para reclamar tus recompensas de rango", "/reclaim");
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . "§cEste comando solo puede ser usado en el juego.");
            return;
        }

        if (!$sender->hasPermission("use.player.command")) {
            $sender->sendMessage($prefix . "§cNo tienes permiso para usar este comando.");
            return;
        }

        $cooldownFile = new Config(HCFLoader::getInstance()->getDataFolder() . "reclaim_cooldowns.json", Config::JSON);
        $name = $sender->getName();
        $lastUsed = $cooldownFile->get($name, 0);
        $now = time();

        if ($now - $lastUsed < self::GLOBAL_COOLDOWN) {
            $remaining = self::GLOBAL_COOLDOWN - ($now - $lastUsed);
            $sender->sendMessage($prefix . "§cDebes esperar §e" . Cooldown::format($remaining) . " §cantes de volver a usar §6/reclaim§c.");
            return;
        }

        $hasReclaimed = false;

        foreach ($this->reclaims as $rank => $data) {
            if ($sender->hasPermission($data["permission"])) {
                foreach ($data["rewards"] as $reward) {
                    $crate = Main::getCrate($reward["crate"]);
                    if ($crate === null) {
                        $sender->sendMessage($prefix . "§cCrate no encontrada: §e" . $reward["crate"]);
                        continue;
                    }

                    $item = $crate->getKeyItem($reward["amount"]);
                    $leftovers = $sender->getInventory()->addItem($item);

                    foreach ($leftovers as $leftover) {
                        $sender->getWorld()->dropItem($sender->getPosition(), $leftover);
                    }
                }
                $sender->sendMessage($prefix . $data["message"]);
                $hasReclaimed = true;
            }
        }

        if (!$hasReclaimed) {
            $sender->sendMessage($prefix . "§cNo tienes ningún rango para reclamar.");
            return;
        }

        $cooldownFile->set($name, $now);
        $cooldownFile->save();
    }
}