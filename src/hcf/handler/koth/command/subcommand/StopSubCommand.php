<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StopSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $kothManager = HCFLoader::getInstance()->getKothManager();

        if ($kothManager->getKothActive() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo hay ningún KoTH activo.'));
            return;
        }

        $kothManager->setKothActive(null);
        $sender->sendMessage(TextFormat::colorize('&cHas desactivado el KoTH en curso.'));
    }
}