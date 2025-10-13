<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetPointsSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth setpoints <nombre> <puntos>'));
            return;
        }

        $name = $args[0];
        $points = $args[1];

        $kothManager = HCFLoader::getInstance()->getKothManager();
        $koth = $kothManager->getKoth($name);

        if ($koth === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KoTH no existe.'));
            return;
        }

        if (!is_numeric($points)) {
            $sender->sendMessage(TextFormat::colorize('&cCantidad de puntos inválida.'));
            return;
        }

        $koth->setPoints((int)$points);
        $sender->sendMessage(TextFormat::colorize("&aHas cambiado los puntos a $points del KoTH $name."));
    }
}