<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\koth\KothCapzone;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class SetCapzoneSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (count($args) < 1) {
            $creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName());
            if ($creator !== null && $creator->getType() === 'capzone') {
                if (!$creator->isValid()) {
                    $sender->sendMessage(TextFormat::colorize('&cNo has seleccionado una capzone válida.'));
                    return;
                }
                $creator->deleteCorners($sender);
                $koth = HCFLoader::getInstance()->getKothManager()->getKoth($creator->getName());
                $koth->setCapzone(new KothCapzone(
                    $creator->getMinX(),
                    $creator->getMaxX(),
                    $creator->getMinY(),
                    $creator->getMaxY(),
                    $creator->getMinZ(),
                    $creator->getMaxZ(),
                    $creator->getWorld()
                ));
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&aHas establecido la capzone para el KoTH ' . $creator->getName()));

                foreach ($sender->getInventory()->getContents() as $slot => $item) {
                    if ($item->getNamedTag()->getTag('claim_type')) {
                        $sender->getInventory()->clear($slot);
                        break;
                    }
                }
                return;
            }
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth setcapzone <nombre>'));
            return;
        }

        if ($args[0] === 'cancel') {
            $creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName());
            if ($creator !== null && $creator->getType() === 'capzone') {
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&cHas cancelado la creación de la capzone.'));
            } else {
                $sender->sendMessage(TextFormat::colorize('&cNo estás en modo creación de capzone.'));
            }
            return;
        }

        $name = $args[0];

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa estás creando una capzone.'));
            return;
        }

        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KoTH no existe.'));
            return;
        }

        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eSelector de claim para capzone'));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', 'capzone'));

        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes añadir el selector de capzone a tu inventario.'));
            return;
        }

        $sender->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($sender->getName(), $name, 'capzone');
        $sender->sendMessage(TextFormat::colorize('&aAhora puedes seleccionar la capzone con la azada.'));
    }
}