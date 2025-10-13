<?php

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetLeaderSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction.'));
            return;
        }
        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if ($faction->getRole((string)$sender->getUniqueId()) !== Faction::LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cNo eres el líder actual de la faction.'));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /f setleader [jugador]'));
            return;
        }

        $session = null;
        $p = null;
        $player = $sender->getServer()->getPlayerExact($args[0]);

        if ($player instanceof Player) {
            if ($player->getId() === $sender->getId()) {
                $sender->sendMessage(TextFormat::colorize('&cNo puedes darte el lider a ti mismo.'));
                return;
            }

            if ($player->getSession()->getFaction() !== $faction->getName()) {
                $sender->sendMessage(TextFormat::colorize('&cEl jugador no es miembro de tu faction.'));
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

        if ($faction->getRole($session->getUuid()) === Faction::LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cEste jugador ya es el líder.'));
            return;
        }

        $faction->addRole($session->getUuid(), Faction::LEADER);
        $faction->removeRole((string)$sender->getUniqueId());
        $faction->addRole((string)$sender->getUniqueId(), Faction::MEMBER);

        $sender->sendMessage(TextFormat::colorize('&aHas dado el líder a ' . $session->getName()));

        if ($p !== null && $p->isOnline()) {
            $p->sendMessage(TextFormat::colorize('&aAhora eres el líder de la faction.'));
        }
    }
}