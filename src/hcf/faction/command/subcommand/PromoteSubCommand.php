<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PromoteSubCommand implements FactionSubCommand
{
    /**
     *
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction.'));
            return;
        }

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if (!in_array($faction->getRole((string)$sender->getUniqueId()), [Faction::LEADER, Faction::CO_LEADER])) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes permisos para promotear miembros.'));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f promote [jugador]'));
            return;
        }

        $session = null;
        $p = null;
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if ($player instanceof Player) {
            if ((string)$player->getUniqueId() === (string)$sender->getUniqueId()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes promotearte a ti mismo.'));
                return;
            }

            if ($player->getSession()->getFaction() !== $faction->getName()) {
                $sender->sendMessage(TextFormat::colorize('&cEse jugador no está en tu faction.'));
                return;
            }

            $session = $player->getSession();
            $p = $player;
        } else {
            foreach ($faction->getMembers() as $member) {
                if ($member->getName() === $args[0]) {
                    $session = $member;
                    break;
                }
            }

            if ($session === null) {
                $sender->sendMessage(TextFormat::colorize('&cMiembro no encontrado.'));
                return;
            }
        }

        if ($faction->getRole($session->getUuid()) === Faction::CO_LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cEse miembro ya tiene el rango más alto posible (co-líder).'));
            return;
        }

        if ($faction->getRole((string)$sender->getUniqueId()) === Faction::CO_LEADER) {
            if (in_array($faction->getRole($session->getUuid()), [Faction::LEADER, Faction::CO_LEADER])) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes promotear a este miembro.'));
                return;
            }
        }

        $newRole = match ($faction->getRole($session->getUuid())) {
            Faction::MEMBER => Faction::CAPTAIN,
            Faction::CAPTAIN => Faction::CO_LEADER,
            default => null
        };

        if ($newRole === null) {
            $sender->sendMessage(TextFormat::colorize('&cEste jugador no puede ser promoteado más.'));
            return;
        }

        $faction->addRole($session->getUuid(), $newRole);

        $sender->sendMessage(TextFormat::colorize('&aHas promoteado a &e' . $session->getName() . '&a a &b' . $newRole));

        if ($p !== null && $p->isOnline()) {
            $p->sendMessage(TextFormat::colorize('&aHas sido promoteado a &b' . $newRole));
        }
    }
}