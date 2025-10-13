<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class MapSubCommand implements FactionSubCommand
{
    /**
     *
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() === null) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes una faction.'));
            return;
        }

        $factionName = $sender->getSession()->getFaction();
        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($factionName);
        $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($faction->getName());

        if ($claim === null) {
            $sender->sendMessage(TextFormat::colorize('&cTu faction no tiene un claim.'));
            return;
        }

        $corners = [
            [$claim->getMaxX(), $claim->getMaxZ()],
            [$claim->getMinX(), $claim->getMinZ()],
            [$claim->getMinX(), $claim->getMaxZ()],
            [$claim->getMaxX(), $claim->getMinZ()]
        ];

        foreach ($corners as [$x, $z]) {
            for ($y = $sender->getPosition()->getFloorY(); $y <= 127; $y++) {
                $block = $y % 3 === 0 ? VanillaBlocks::GOLD() : VanillaBlocks::GLASS();
                $sender->getNetworkSession()->sendDataPacket(
                    $this->sendFakeBlock(new Position($x, $y, $z, $sender->getWorld()), $block)
                );
            }
        }

        $sender->sendMessage(TextFormat::colorize('&aSe han mostrado los bordes de tu claim.'));
    }

    /**
     * Envía un bloque falso al jugador.
     *
     * @param Position $position
     * @param Block $block
     * @return UpdateBlockPacket
     */
    private function sendFakeBlock(Position $position, Block $block): UpdateBlockPacket
    {
        $blockPos = BlockPosition::fromVector3($position->asVector3());
        $blockId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId());

        return UpdateBlockPacket::create(
            $blockPos,
            $blockId,
            UpdateBlockPacket::FLAG_NETWORK,
            UpdateBlockPacket::DATA_LAYER_NORMAL
        );
    }
}