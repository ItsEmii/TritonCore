<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class LeaveSubCommand implements FactionSubCommand
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

        if ($faction->getRole((string)$sender->getUniqueId()) === Faction::LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cEres el líder de la faction'));
            return;
        }
        
        if (HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction())->getTimeRegeneration() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes usar esto con el tiempo de regeneración activo'));
            return;
        }
        
        
        $faction->removeRole((string)$sender->getUniqueId());
        
        
        $faction->setDtr($faction->getDtr() - 1.00); 
        

        $sender->getSession()->setFaction(null);
        $sender->getSession()->setFactionChat(false);
        
        $sender->setScoreTag('');
        $sender->sendMessage(TextFormat::colorize('&cHas salido de tu faction'));
    }

    public function getAlias(): ?string
    {
        return "leave"; 
    }

    public function getPermission(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return "leave";
    }

    public function getDescription(): string
    {
        return "Salir de tu faction actual.";
    }
}