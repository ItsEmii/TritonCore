<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DemoteSubCommand implements FactionSubCommand
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

        if (!in_array($faction->getRole((string)$sender->getUniqueId()), [Faction::LEADER, Faction::CO_LEADER])) {
            $sender->sendMessage(TextFormat::colorize('&cNo eres líder ni co-líder para demotear a miembros'));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f demote [jugador]'));
            return;
        }

        $session = null;
        $p = null;
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if ($player instanceof Player) {
            if ($player->getId() === $sender->getId()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes demotearte a ti mismo'));
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

        if ($faction->getRole((string)$session->getUuid()) === Faction::MEMBER) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes demotear a un miembro'));
            return;
        }

        $roles = [
            Faction::CO_LEADER => Faction::CAPTAIN,
            Faction::CAPTAIN => Faction::MEMBER
        ];

        if ($faction->getRole((string)$sender->getUniqueId()) === Faction::CO_LEADER) {
            if ($faction->getRole($session->getUuid()) === Faction::LEADER || $faction->getRole($session->getUuid()) === Faction::CO_LEADER) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes demotear a este jugador'));
                return;
            }
        }

        $faction->addRole($session->getUuid(), $roles[$faction->getRole($session->getUuid())]);

        $sender->sendMessage(TextFormat::colorize('&aHas demoteado a ' . $session->getName()));

        if ($p !== null && $p->isOnline())
            $p->sendMessage(TextFormat::colorize('&cHas sido demoteado en tu facción'));
    }
}