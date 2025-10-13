<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class ClaimSubCommand implements FactionSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction'));
            return;
        }

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($sender->getSession()->getFaction());

        if ($faction->getRole((string)$sender->getUniqueId()) !== 'leader') {
            $sender->sendMessage(TextFormat::colorize('&cNo eres el líder de la faction para claimear'));
            return;
        }

        if (count($args) < 1) {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null && $creator->getType() === 'faction') {
                if (!$creator->isValid()) {
                    $sender->sendMessage(TextFormat::colorize('&cNo has seleccionado el claim'));
                    return;
                }

                $balance = $faction->getBalance() - $creator->calculateValue();

                if ($balance < 0) {
                    $sender->sendMessage(TextFormat::colorize('&cTu faction no tiene suficiente dinero para pagar el claim'));
                    return;
                }

                $creator->deleteCorners($sender);
                HCFLoader::getInstance()->getClaimManager()->createClaim($creator->getName(), $creator->getType(), $creator->getMinX(), $creator->getMaxX(), $creator->getMinZ(), $creator->getMaxZ(), $creator->getWorld());
                $sender->sendMessage(TextFormat::colorize('&aHas claimeado exitosamente'));
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());

                foreach ($sender->getInventory()->getContents() as $slot => $i) {
                    if ($i->getNamedTag()->getTag('claim_type')) {
                        $sender->getInventory()->clear($slot);
                        break;
                    }
                }
                return;
            }
        }

        if (count($args) !== 0) {
            if ($args[0] === 'cancel') {
                if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null) {
                    $creator->deleteCorners($sender);
                    HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                    $sender->sendMessage(TextFormat::colorize('&cHas cancelado el claim'));
                } else {
                    $sender->sendMessage(TextFormat::colorize('&cAún no estás en claimhud'));
                }
                return;
            }
        }

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa estás creando un claim'));
            return;
        }

        if (HCFLoader::getInstance()->getClaimManager()->getCreateByClaimName($faction->getName())) {
            $sender->sendMessage(TextFormat::colorize('&cAlguien de tu faction ya está haciendo un claim'));
            return;
        }

        if (HCFLoader::getInstance()->getClaimManager()->getClaim($faction->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cTu faction ya tiene un claim'));
            return;
        }

        $pos = $sender->getPosition();
        $distance = sqrt(pow($pos->getX(), 2) + pow($pos->getZ(), 2));
        if ($distance < 300) {
            $sender->sendMessage(TextFormat::colorize('&cNecesitas estar a más de 300 bloques del spawn para empezar un claim.'));
            return;
        }

        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eSelector de claim'));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', 'faction'));

        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes añadir el item para hacer el claim en tu inventario'));
            return;
        }

        $sender->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($sender->getName(), $faction->getName(), 'faction');
        $sender->sendMessage(TextFormat::colorize('&aAhora puedes claimear'));
    }
}