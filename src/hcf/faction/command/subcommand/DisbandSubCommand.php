<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DisbandSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction'));
            return;
        }
        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if ($faction->getRole((string)$sender->getUniqueId()) !== Faction::LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cNo eres el líder, por lo que no puedes disolver la faction'));
            return;
        }
        
        if ($faction->getTimeRegeneration() !== null) {
            $sender->sendMessage(TextFormat::colorize("&cNo puedes usar esto mientras el tiempo de regeneración esté activo"));
            return;
        }
        
        foreach ($sender->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof Player) {
                if ($player->getSession()->getFaction() === $sender->getSession()->getFaction()) {
                    $player->sendMessage(TextFormat::colorize("&cTu faction ha sido disuelta por el líder."));
                }
            }
        }

        $factionName = $faction->getName();
        $faction->disband();
        HCFLoader::getInstance()->getFactionManager()->removeFaction($factionName);
        
        $sender->sendMessage(TextFormat::colorize('&aLa faction ha sido disuelta correctamente'));
        $sender->getServer()->broadcastMessage(TextFormat::colorize("&eLa faction &9$factionName &eha sido disuelta por &f{$sender->getName()}"));
    }
}