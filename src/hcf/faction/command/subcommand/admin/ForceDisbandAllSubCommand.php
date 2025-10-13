<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ForceDisbandAllSubCommand implements FactionSubCommand
{
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender->hasPermission('op.cmd')) {
            return;
        }

        $exceptions = [
            'Spawn',
            'South Road',
            'West Road',
            'East Road',
            'North Road',

        ];

        foreach(HCFLoader::getInstance()->getFactionManager()->getFactions() as $faction) {
            if (in_array($faction->getName(), $exceptions, true)) {
                continue;
            }

            $faction->disband();
            HCFLoader::getInstance()->getFactionManager()->removeFaction($faction->getName());
            HCFLoader::getInstance()->getServer()->broadcastMessage(
                TextFormat::colorize("&7[&5Legends&7] &cLa faction &e" . $faction->getName() . " &cha sido disuelta por un administrador."));

        }
    }
}
