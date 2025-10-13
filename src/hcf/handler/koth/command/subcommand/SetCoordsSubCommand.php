<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SetCoordsSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth setcoords <nombre>'));
            return;
        }

        $name = $args[0];
        $koth = HCFLoader::getInstance()->getKothManager()->getKoth($name);

        if ($koth === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KoTH no existe.'));
            return;
        }

        $coords = $sender->getPosition()->getFloorX() . ', ' . $sender->getPosition()->getFloorZ();
        $koth->setCoords($coords);

        $sender->sendMessage(TextFormat::colorize("&aHas seleccionado las coordenadas del KoTH $name: $coords"));
    }
}