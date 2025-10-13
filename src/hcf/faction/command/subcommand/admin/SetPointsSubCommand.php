<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetPointsSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender->hasPermission('op.cmd')) {
            return;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cUsa: §f/faction setpoints [nombre] [puntos]"));
            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cEl valor de puntos debe ser numérico."));
            return;
        }

        $name = $args[0];
        $points = (int) $args[1];

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($name);
        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cNo existe la faction llamada §f{$name}§c."));
            return;
        }

        $faction->setPoints($points);
        $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §aLos puntos de la faction §f{$name} §ahan sido actualizados a §f{$points}§a."));

        $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('admin.webhook'));
        $msg = new Message();
        $msg->setContent("Los puntos de la faction **{$name}** han sido cambiados a **{$points}** por el staff **{$sender->getName()}**");
        $webHook->send($msg);
    }
}