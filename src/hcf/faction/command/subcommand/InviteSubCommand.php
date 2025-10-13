<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class InviteSubCommand implements FactionSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&cNo eres líder, co-líder o capitán para invitar'));
            return;
        }

        if (count($faction->getRoles()) === HCFLoader::getInstance()->getConfig()->get('faction.max.members', 4)) {
            $sender->sendMessage(TextFormat::colorize('&cTu faction tiene el máximo de jugadores'));
            return;
        }
        
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f invite [jugador]'));
            return;
        }
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);
        
        if (!$player instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cJugador no encontrado'));
            return;
        }
        
        if ($player->getSession()->getFaction() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cEl jugador ya tiene una faction'));
            return;
        }

        if (HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction())->getTimeRegeneration() !== null) {
            $sender->sendMessage(TextFormat::colorize("&cNo puedes usar esto con el tiempo de regeneración activo"));
            return;
        }
        HCFLoader::getInstance()->getFactionManager()->createInvite($sender, $player);
        $player->sendMessage(TextFormat::colorize('&a' . $sender->getName() . ' te ha invitado a unirte a la faction ' . $sender->getSession()->getFaction()));
        $sender->sendMessage(TextFormat::colorize('&aHas invitado a ' . $player->getName() . ' a unirse a tu faction'));
    }
}