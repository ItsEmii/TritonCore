<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ForceDisbandSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender->hasPermission('op.cmd')) {
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cUsa /faction forcedisband [nombre]"));
            return;
        }

        $name = $args[0];

        if (HCFLoader::getInstance()->getFactionManager()->getFaction($name) === null) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cNo existe la faction que estás intentando disolver."));
            return;
        }

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($name);
        $faction->disband();
        HCFLoader::getInstance()->getFactionManager()->removeFaction($name);

        $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §aLa faction §e" . $name . " §aha sido disuelta correctamente."));

        $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('admin.webhook'));
        $msg = new Message();
        $msg->setContent("The **{$name}** faction was disbanded by staff **{$sender->getName()}**");
        $webHook->send($msg);
    }
}