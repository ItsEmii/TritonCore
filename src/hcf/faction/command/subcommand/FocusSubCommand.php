<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use hcf\utils\waypoint\WayPoint;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

/**
 * Class FocusSubCommand
 * @package hcf\faction\command\subcommand
 */
class FocusSubCommand implements FactionSubCommand
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
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /faction focus [nombre]'));
            return;
        }
        $name = $args[0];
        $factionName = null;
        $player = $sender->getServer()->getPlayerByPrefix($name);
        
        if ($player instanceof Player) {
            if ($player->getSession()->getFaction() === null) {
                $sender->sendMessage(TextFormat::colorize('&cEl jugador al que intentas focusear no tiene faction'));
                return;
            }
            
            if ($player->getName() === $sender->getName()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes focusear a ti mismo'));
                return;
            }
            
            if ($player->getSession()->getFaction() === $faction->getName()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes focusear los miembros de tu propia faction'));
                return;
            }
            $factionName = $player->getSession()->getFaction();
        } else {
            if (HCFLoader::getInstance()->getFactionManager()->getFaction($name) === null) {
                $sender->sendMessage(TextFormat::colorize('&cNo existe la faction que intentas focusear'));
                return;
            }
            
            if ($name === $sender->getSession()->getFaction()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes focusear a tu propia faction'));
                return;
            }
            $factionName = $name;
        }
        $faction->setFocus($factionName);
        $session = HCFLoader::getInstance()->getSessionManager()->getSession((string)$sender->getUniqueId());
        
        $focus = HCFLoader::getInstance()->getFactionManager()->getFaction($factionName);

        
        $sender->sendMessage(TextFormat::colorize('&aAhora tu faction está focuseando a la faction ' . $factionName));
    }
}