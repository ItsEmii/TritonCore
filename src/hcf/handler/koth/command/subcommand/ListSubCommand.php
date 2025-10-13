<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        $kothManager = HCFLoader::getInstance()->getKothManager();
        $sender->sendMessage(TextFormat::colorize('&e§l× KOTH LIST ×§r'));

        foreach ($kothManager->getKoths() as $name => $koth) {
            $coords = $koth->getCoords() ?? '§cNo coordinates set';
            $sender->sendMessage(TextFormat::colorize("&c{$name} &f{$coords}"));
        }
    }
}