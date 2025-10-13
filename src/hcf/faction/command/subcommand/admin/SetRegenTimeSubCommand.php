<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetRegenTimeSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender->hasPermission('op.cmd')) {
            return;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cUsa: §f/faction setregentime [nombre] [tiempo en minutos]"));
            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cEl tiempo debe ser un número válido."));
            return;
        }

        $name = $args[0];
        $time = (int)$args[1];

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($name);
        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cNo existe la faction llamada §f{$name}§c."));
            return;
        }

        $faction->setTimeRegeneration($time * 60);

        $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §aEl tiempo de regeneración de la faction §f{$name} §aahora es §f{$time} minutos§a."));

        $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('admin.webhook'));
        $msg = new Message();
        $msg->setContent("El tiempo de regeneración de la faction **{$name}** ahora es **{$time} minutos** por el staff **{$sender->getName()}**");
        $webHook->send($msg);
    }
}