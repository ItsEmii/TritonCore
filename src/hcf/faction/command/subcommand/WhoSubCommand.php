<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class WhoSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        $faction = null;

        if (!isset($args[0])) {
            if ($sender->getSession()->getFaction() === null) {
                $sender->sendMessage(TextFormat::colorize('&cNo tienes una Faction'));
                return;
            }
            $faction = $sender->getSession()->getFaction();
        } else {
            $target = $sender->getServer()->getPlayerByPrefix($args[0]);

            if ($target instanceof Player) {
                if ($target->getSession()->getFaction() === null) {
                    $sender->sendMessage(TextFormat::colorize('El jugador no tiene Faction'));
                    return;
                }
                $faction = $target->getSession()->getFaction();
            } else {
                if (HCFLoader::getInstance()->getFactionManager()->getFaction($args[0])) {
                    $faction = $args[0];
                }
            }
        }

        if ($faction === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo se encontró la Faction'));
            return;
        }
        $factionInstance = HCFLoader::getInstance()->getFactionManager()->getFaction($faction);

        $message = '--------------------' . "\n";
        $message .= '§e' . $factionInstance->getName() . ' &6[§1' . count($factionInstance->getOnlineMembers()) . '/' . count($factionInstance->getMembers()) . '§6] &3- &eHQ: &f' . 
            ($factionInstance->getHome() !== null ? 'X: ' . $factionInstance->getHome()->getFloorX() . ' Z: ' . $factionInstance->getHome()->getFloorZ() : 'No establecida') . "\n";
        
        $message .= '§e▐  §1Leader: &f' . implode(', ', array_map(function ($session) {
            return ($session->isOnline() ? '&a' : '&c') . $session->getName() . ' &6[§1' . $session->getKills() . '§6]';
        }, $factionInstance->getMembersByRole(Faction::LEADER))) . "\n";

        $message .= '§e▐  §1Coleaders: &f' . implode(', ', array_map(function ($session) {
            return ($session->isOnline() ? '&a' : '&c') . $session->getName() . ' &6[§e' . $session->getKills() . '§6]';
        }, $factionInstance->getMembersByRole(Faction::CO_LEADER))) . "\n";

        $message .= '§e▐  §1Captains: &f' . implode(', ', array_map(function ($session) {
            return ($session->isOnline() ? '&a' : '&c') . $session->getName() . ' &6[§e' . $session->getKills() . '§6]';
        }, $factionInstance->getMembersByRole(Faction::CAPTAIN))) . "\n";

        $message .= '§e▐  §1Members: &f' . implode(', ', array_map(function ($session) {
            return ($session->isOnline() ? '&a' : '&c') . $session->getName() . ' &6[§e' . $session->getKills() . '§6]';
        }, $factionInstance->getMembersByRole(Faction::MEMBER))) . "\n";

        $message .= '§e▐  §1Balance: &a$' . $factionInstance->getBalance() . "\n";

        $dtrColor = $factionInstance->getDtr() >= $factionInstance->getMaxDtr() ? '&a' : ($factionInstance->getDtr() <= 0.00 ? '&c' : '&e');
        $message .= '§e▐  §1DTR: ' . $dtrColor . round($factionInstance->getDtr(), 2) . '■' . "\n";

        if ($factionInstance->getTimeRegeneration() !== null) {
            $message .= '§e▐  §1Until Regen: &9' . gmdate('H:i:s', $factionInstance->getTimeRegeneration()) . "\n";
        }

        $message .= '§e▐  §1Points: &9' . $factionInstance->getPoints() . "\n";
        $message .= '§e▐  §1KoTH Captures: &9' . $factionInstance->getKothCaptures() . "\n";
        $message .= '§e▐  §1Strikes: &9' . $factionInstance->getStrikes() . "\n";
        $message .= '--------------------';

        $sender->sendMessage(TextFormat::colorize($message));
    }
}