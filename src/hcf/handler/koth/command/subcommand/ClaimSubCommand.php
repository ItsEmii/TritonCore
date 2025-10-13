<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class ClaimSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cEste comando solo puede usarse en el juego.'));
            return;
        }
        
        $claimManager = HCFLoader::getInstance()->getClaimManager();
        
        if (count($args) < 1) {
            $creator = $claimManager->getCreator($sender->getName());
            if ($creator !== null && $creator->getType() === 'koth') {
                if (!$creator->isValid()) {
                    $sender->sendMessage(TextFormat::colorize('&cNo has seleccionado correctamente el claim.'));
                    return;
                }
                $creator->deleteCorners($sender);
                $claimManager->createClaim(
                    $creator->getName(),
                    $creator->getType(),
                    $creator->getMinX(),
                    $creator->getMaxX(),
                    $creator->getMinZ(),
                    $creator->getMaxZ(),
                    $creator->getWorld()
                );
                $sender->sendMessage(TextFormat::colorize('&aHas creado el claim del KOTH ' . $creator->getName()));
                $claimManager->removeCreator($sender->getName());

                foreach ($sender->getInventory()->getContents() as $slot => $item) {
                    $tag = $item->getNamedTag();
                    if ($tag->getString('claim_type') === 'koth') {
                        $sender->getInventory()->clear($slot);
                        break;
                    }
                }
                return;
            }
            $sender->sendMessage(TextFormat::colorize('&cUso correcto: /koth claim [nombre]'));
            return;
        }

        if ($args[0] === 'cancel') {
            $creator = $claimManager->getCreator($sender->getName());
            if ($creator !== null && $creator->getType() === 'koth') {
                $claimManager->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&cHas cancelado el modo claim.'));
            } else {
                $sender->sendMessage(TextFormat::colorize('&cNo estás en modo claim.'));
            }
            return;
        }

        $name = $args[0];

        if ($claimManager->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa estás creando un claim.'));
            return;
        }

        if (HCFLoader::getInstance()->getKothManager()->getKoth($name) === null) {
            $sender->sendMessage(TextFormat::colorize('&cEl KOTH no existe.'));
            return;
        }

        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eSelector de claim'));
        $nbt = new CompoundTag();
        $nbt->setString('claim_type', 'koth');
        $item->setNamedTag($nbt);

        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes espacio en el inventario para el selector de claim.'));
            return;
        }
        $sender->getInventory()->addItem($item);
        $claimManager->createCreator($sender->getName(), $name, 'koth');
        $sender->sendMessage(TextFormat::colorize('&aAhora puedes seleccionar el área del claim con la azada dorada.'));
    }
}