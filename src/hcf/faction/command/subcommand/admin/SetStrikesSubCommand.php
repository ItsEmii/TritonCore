<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetStrikesSubCommand implements FactionSubCommand
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

        if (count($args) < 3) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cUsa: §f/faction setstrikes [nombre] [cantidad] [motivo]"));
            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cLa cantidad de strikes debe ser un número válido."));
            return;
        }

        $name = $args[0];
        $strikes = (int)$args[1];
        $motive = $args[2];

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($name);
        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §cNo existe la faction llamada §f{$name}§c."));
            return;
        }

        $faction->setStrikes($strikes);

        $sender->sendMessage(TextFormat::colorize("§7[§5Legends§7] §aLos strikes de la faction §f{$name} §aahora son §f{$strikes} §acon motivo: §f{$motive}"));

        $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('admin.webhook'));
        $msg = new Message();

        $embed = new Embed();
        $embed->setTitle("New Faction Strike");
        $embed->setColor(0x890000);
        $embed->addField("Faction 👥", $name);
        $embed->addField("Strikes 🚨", (string)$strikes, true);
        $embed->addField("Motive 📢", $motive, true);
        $embed->setFooter("");
        $msg->addEmbed($embed);

        $webHook->send($msg);
    }
}