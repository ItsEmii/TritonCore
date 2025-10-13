<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DeleteSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cEste comando solo se puede usar en juego.'));
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth delete [nombre]'));
            return;
        }

        $name = $args[0];

        $kothManager = HCFLoader::getInstance()->getKothManager();
        if ($kothManager->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KOTH "' . $name . '" no existe.'));
            return;
        }

        $kothManager->removeKoth($name);
        $sender->sendMessage(TextFormat::colorize('&aHas eliminado correctamente el KOTH "' . $name . '".'));
    }
}