<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ChatSubCommand implements FactionSubCommand
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

        if ($sender->getSession()->hasFactionChat() === false) {
            $sender->getSession()->setFactionChat(true);
            $sender->sendMessage(TextFormat::GREEN . "¡Ahora estás en el chat de la faction!");
        } else {
            $sender->getSession()->setFactionChat(false);
            $sender->sendMessage(TextFormat::RED . "¡Ahora estás en el chat público!");
        }
    }

    public function getAlias(): ?string
    {
        return "c";
    }

    public function getPermission(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return "chat";
    }

    public function getDescription(): string
    {
        return "Usa el comando para chatear en la faction";
    }
}