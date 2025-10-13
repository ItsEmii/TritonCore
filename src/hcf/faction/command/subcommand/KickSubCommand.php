<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KickSubCommand implements FactionSubCommand
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
        
        if (!in_array($faction->getRole((string)$sender->getUniqueId()), [Faction::LEADER, Faction::CO_LEADER, Faction::CAPTAIN])) {
            $sender->sendMessage(TextFormat::colorize('&cNo eres líder, co-líder o capitán para kickear'));
            return;
        }
        
        if (HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction())->getTimeRegeneration() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes usar esto con el tiempo de regeneración activo'));
            return;
        }
        
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f kick [jugador]'));
            return;
        }
        $session = null;
        $p = null;
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);
        
        if ($player instanceof Player) {
            if ($player->getId() === $sender->getId()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes kickearte a ti mismo'));
                return;
            }
            
            if ($player->getSession()->getFaction() !== $faction->getName()) {
                $sender->sendMessage(TextFormat::colorize('&cEl jugador no es miembro de tu faction'));
                return;
            }
            $session = $player->getSession();
            $p = $player;
        } else {
            $members = $faction->getMembers();
            
            foreach ($members as $member) {
                if ($member->getName() === $args[0]) {
                    $session = $member;
                    break;
                }
            }
            
            if ($session === null) {
                $sender->sendMessage(TextFormat::colorize('&cMiembro no encontrado'));
                return;
            }
        }
        
        if ($faction->getRole((string)$sender->getUniqueId()) === Faction::CO_LEADER) {
            if ($faction->getRole($session->getUuid()) === Faction::LEADER || $faction->getRole($session->getUuid()) === Faction::CO_LEADER) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes kickear a este jugador'));
                return;
            }
        }
        $faction->removeRole($session->getUuid());
        $faction->setDtr($faction->getDtr() - 1.00); 

        $session->setFactionChat(false);
        $session->setFaction(null);
        
        if ($p !== null && $p->isOnline()) {
            $p->setScoreTag('');
            $p->sendMessage(TextFormat::colorize('&cHas sido kickeado de tu faction'));
        }
        $sender->sendMessage(TextFormat::colorize('&cHas kickeado al jugador'));
        
    }
}