<?php

declare(strict_types=1);

namespace hcf\handler\kit\command\subcommand;

use hcf\handler\kit\command\KitSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\inventorie\Inventories;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class EditSubCommand
 * @package hcf\handler\kit\command\subcommand
 */
class EditSubCommand implements KitSubCommand
{
    
    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize("&cUse: /gkit edit [category: free|pay|legendary]"));
            return;
        }

        if ($args[0] === "free") {
            Inventories::editKitOrganization($sender);
        } elseif ($args[0] === "pay") {
            Inventories::editKitPayOrganization($sender);
        } elseif ($args[0] === "legendary") {
            Inventories::editKitLOrganization($sender);
        } else {
            $sender->sendMessage(TextFormat::colorize("&cCategory is not valid."));
        }
    }
}