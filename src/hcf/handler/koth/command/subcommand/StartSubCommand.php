<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\sounds\Sounds;
use hcf\utils\Utils;
use hcf\utils\time\Timer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StartSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth start <nombre>'));
            return;
        }

        $name = $args[0];
        $kothManager = HCFLoader::getInstance()->getKothManager();
        $koth = $kothManager->getKoth($name);

        if ($kothManager->getKothActive() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa hay un KoTH activo.'));
            return;
        }

        if ($koth === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KoTH no existe.'));
            return;
        }

        if ($koth->getCapzone() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo se ha definido una zona de captura.'));
            return;
        }

        $location = $koth->getCoords();
        $time = $koth->getTime() / 60;
        $points = $koth->getPoints();

        if ($sender instanceof Player) {
            $session = $sender->getSession();
            $cooldown = $session->getCooldown("koth.cooldown");

            if ($cooldown !== null) {
                $remaining = $cooldown->getTime();
                $sender->sendMessage(TextFormat::colorize('&cDebes esperar ' . Timer::getTimeToString($remaining) . ' antes de iniciar otro KoTH.'));
                return;
            }

            $session->addCooldown("koth.cooldown", "", Timer::time("24h"), false, false);
        }

        $kothManager->setKothActive($name);

        $starter = $sender->getName();
        HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("&e¡KoTH $name ha sido activado por $starter en $location!"));
        $sender->sendMessage(TextFormat::colorize("&aHas activado el KoTH $name."));

        $webhookUrl = HCFLoader::getInstance()->getConfig()->get('koth.webhook');
        $webHook = new Webhook($webhookUrl);

        Utils::kothstart();

        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("¡El KoTH $name ha sido activado!");
        $embed->setColor(0xFF0000);
        $embed->addField("Activador", $starter);
        $embed->addField("Posición", $location);
        $embed->addField("Tiempo de Captura", "$time minutos");
        $embed->addField("> IP", "legendsmc.fun");
        $embed->addField("> Puerto", "19132");
        $embed->setFooter("LEGENDSMC");
        $msg->addEmbed($embed);
        $msg->setContent("@here");

        $webHook->send($msg);
    }

    public function sendCaptureMessage(string $kothName, string $playerName): void
    {
        $webhookUrl = HCFLoader::getInstance()->getConfig()->get('koth.webhook');
        $webHook = new Webhook($webhookUrl);

        HCFLoader::getInstance()->getServer()->broadcastMessage(TextFormat::colorize("¡El KoTH $kothName fue capturado por $playerName!"));

        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("¡KoTH $kothName Capturado!");
        $embed->setColor(0xFF0000);
        $embed->addField("Capturado por", $playerName);
        $msg->addEmbed($embed);

        $webHook->send($msg);
    }
}