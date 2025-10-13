<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnclaimSubCommand implements FactionSubCommand
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
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una Faction'));
            return;
        }

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if ($faction->getRole((string)$sender->getUniqueId()) !== Faction::LEADER) {
            $sender->sendMessage(TextFormat::colorize('&cSolo el Lider puede remover el Claim'));
            return;
        }

        if ($faction->getTimeRegeneration() !== null) {
            $sender->sendMessage(TextFormat::colorize("&cNo puedes usar esto con el tiempo de regeneración activo"));
            return;
        }

        HCFLoader::getInstance()->getClaimManager()->removeClaim($faction->getName());
        $sender->sendMessage(TextFormat::colorize('&cHas removido el Claim de tu Faction'));
    }
}