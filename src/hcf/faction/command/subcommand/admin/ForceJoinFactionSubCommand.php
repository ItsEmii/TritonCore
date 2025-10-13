<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand\admin;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as PMPlayer;
use pocketmine\utils\TextFormat;

class ForceJoinFactionSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender->hasPermission("op.cmd")) {
            $sender->sendMessage(TextFormat::colorize("&cNo tienes permiso para usar este comando."));
            return;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::colorize("&cUso: /faction forcejoin [jugador] [factionNamen]"));
            return;
        }

        $playerName = $args[0];
        $factionName = $args[1];

        $server = HCFLoader::getInstance()->getServer();
        $player = $server->getPlayerByPrefix($playerName);

        if (!$player instanceof PMPlayer || !$player->isOnline()) {
            $sender->sendMessage(TextFormat::colorize("&cEl jugador '{$playerName}' no esta en línea."));
            return;
        }

        $session = HCFLoader::getInstance()->getSessionManager()->getSession($player);
        if ($session === null) {
            $sender->sendMessage(TextFormat::colorize("&cNo se pudo obtener la session del jugador."));
            return;
        }

        $factionManager = HCFLoader::getInstance()->getFactionManager();
        $faction = $factionManager->getFaction($factionName);

        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize("&cLa faction '{$factionName}' no existe."));
            return;
        }

        if ($session->getFaction() !== null) {
            $sender->sendMessage(TextFormat::colorize("&c{$playerName} ya estas en una faction."));
            return;
        }

        $session->setFaction($factionName);
        $faction->addMember($player->getName());

        $player->sendMessage(TextFormat::colorize("&aHas sido forzado a unirte a la faction '{$factionName}' por un administrador."));
        $sender->sendMessage(TextFormat::colorize("&aForzaste a {$playerName} a unirse a '{$factionName}'."));
    }
}
