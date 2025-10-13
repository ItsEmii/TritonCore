<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\faction\FactionInvite;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AcceptInviteSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if ($sender->getSession()->getFaction() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa tienes a una faction'));
            return;
        }
        
        if (isset($args[0])) {
            $factionName = (string) $args[0];
            $playerInvites = HCFLoader::getInstance()->getFactionManager()->getInvites((string)$sender->getUniqueId());
            
            if ($playerInvites === null || count($playerInvites) === 0) {
                $sender->sendMessage(TextFormat::colorize('&cNo tienes invitaciones'));
                return;
            }
            $invites = array_filter($playerInvites, function (FactionInvite $invite): bool {
                return $invite->getTime() > time();
            });

            if (!isset($invites[$factionName])) {
                $sender->sendMessage(TextFormat::colorize('&cNo tienes invitaciones de esta faction'));
                return;
            }
            $invite = $invites[$factionName];
            
            if ($invite->getTime() < time()) {
                $sender->sendMessage(TextFormat::colorize('&cEsta invitación ya expiró'));
                HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $factionName);
                return;
            }
            
            if ($invite->getPlayer()->getSession()->getFaction() !== $invite->getFaction()) {
                $sender->sendMessage(TextFormat::colorize('&cInvitación no válida'));
                HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $factionName);
                return;
            }
            $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($factionName);
            
            if (count($faction->getMembers()) >= HCFLoader::getInstance()->getConfig()->get("faction.max.members", 4)) {
                $sender->sendMessage(TextFormat::colorize('&c¿En serio creíste que funcionaría?'));
                return;
            }
    
            if ($faction->getRole($invite->getPlayer()->getName()) === Faction::MEMBER) {
                $sender->sendMessage(TextFormat::colorize('&cInvitación no válida'));
                HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $factionName);
                return;
            }
            $player = $invite->getPlayer();
            
            if ($player->isOnline()) {
                $player->sendMessage(TextFormat::colorize('&a' . $sender->getName() . ' aceptó la invitación'));
            }
            $sender->sendMessage(TextFormat::colorize('&aHas aceptado la invitación de ' . $player->getName()));
            
            $faction->addRole((string)$sender->getUniqueId(), Faction::MEMBER);
            $faction->announce(TextFormat::colorize('&a' . $sender->getName() . ' se unió a la faction'));
            
            $faction->setDtr($faction->getDtr() + 1.00); 
            
            $sender->setScoreTag(TextFormat::colorize('&6[&c' . $faction->getName() . ' ' . ($faction->getDtr() === (count($faction->getMembers()) + 0.1) ? '&a' : '&c') . $faction->getDtr() . '■&6]'));
            $sender->getSession()->setFaction($faction->getName());
    
            HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $factionName);
            return;
        }
        $playerInvites = HCFLoader::getInstance()->getFactionManager()->getInvites((string)$sender->getUniqueId());
        
        if ($playerInvites === null || count($playerInvites) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes invitaciones'));
            return;
        }
        $invites = array_values(array_filter($playerInvites, function (FactionInvite $invite): bool {
            return $invite->getTime() > time();
        }));
        
        if (count($invites) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes invitaciones'));
            return;
        }
        $invite = $invites[0];
        
        if ($invite->getPlayer()->getSession()->getFaction() !== $invite->getFaction()) {
            $sender->sendMessage(TextFormat::colorize('&cInvitación no válida'));
            HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $invite->getFaction());
            return;
        }
        $inviter = $invite->getPlayer();
        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($inviter->getSession()->getFaction());

        if ($faction->getRole($inviter->getName()) === Faction::MEMBER) {
            $sender->sendMessage(TextFormat::colorize('&cInvitación no válida'));
            HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $faction->getName());
            return;
        }

        if ($inviter->isOnline()) {
            $inviter->sendMessage(TextFormat::colorize('&a' . $sender->getName() . ' aceptó la invitación'));
        }
        $sender->sendMessage(TextFormat::colorize('&aHas aceptado la invitación de ' . $inviter->getName()));
        
        $faction->addRole((string)$sender->getUniqueId(), Faction::MEMBER);
        $faction->announce(TextFormat::colorize('&a' . $sender->getName() . ' se unió a la faction'));
        

        $faction->setDtr($faction->getDtr() + 1.00);

        $sender->getSession()->setFaction($faction->getName());

        HCFLoader::getInstance()->getFactionManager()->removeInvite($sender, $faction->getName());
    }

    public function getAlias(): ?string
    {
        return "accept";
    }

    public function getPermission(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return "join";
    }

    public function getDescription(): string
    {
        return "Usa este comando para aceptar la invitación y unirte a una faction.";
    }
}