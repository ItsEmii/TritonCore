<?php

namespace hcf\faction\subclaim;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\Sign;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;

class Chest implements Listener
{
    public function Interact(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if (!$player instanceof Player) return;
        if (!$block instanceof \pocketmine\block\Chest) return;
        if (HCFLoader::getInstance()->getTimerManager()->getPurge()->isActive()) return;

        if ($this->isSubClaim($block)) {
            if (!$this->canOpenSubClaim($player, $block)) {
                $player->sendPopup(TextFormat::colorize("&cNo tienes permiso para abrir este cofre"));
                $event->cancel();
            }
        }
    }

    public function isSubClaim(\pocketmine\block\Chest $chest): bool
    {
        $mgr = HCFLoader::getInstance()->getClaimManager();
        $world = $chest->getPosition()->getWorld();
        $pos = $chest->getPosition();
        $tile = $world->getTileAt($pos->x, $pos->y, $pos->z);
        if (!$tile instanceof TileChest) return false;
        if (!$mgr->insideClaim($pos)) return false;

        $signs = $this->getAdjacentSigns($world, $pos);

        if ($tile->isPaired() && $tile->getPair() instanceof TileChest) {
            $pairPos = $tile->getPair()->getPosition();
            $signs = array_merge($signs, $this->getAdjacentSigns($world, $pairPos));
        }

        foreach ($signs as $sign) {
            if ($sign instanceof Sign) {
                if (trim($sign->getText()->getLine(0)) === "§6[§eSubClaim§6]") {
                    return true;
                }
            }
        }

        return false;
    }

    public function canOpenSubClaim(Player $player, \pocketmine\block\Chest $chest): bool
    {
        $world = $chest->getPosition()->getWorld();
        $pos = $chest->getPosition();
        $tile = $world->getTileAt($pos->x, $pos->y, $pos->z);

        if (!$tile instanceof TileChest) return false;
        if (!$this->isSubClaim($chest)) return false;

        $claimManager = HCFLoader::getInstance()->getClaimManager();
        $claim = $claimManager->insideClaim($pos);
        if ($claim === null) return false;

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($claim);

        if ($faction === null) return false;

        $playerUUID = (string)$player->getUniqueId();
        if ($faction->getRole($playerUUID) === $faction::LEADER) {
            return true;
        }

        $signs = $this->getAdjacentSigns($world, $pos);

        if ($tile->isPaired() && $tile->getPair() instanceof TileChest) {
            $pairPos = $tile->getPair()->getPosition();
            $signs = array_merge($signs, $this->getAdjacentSigns($world, $pairPos));
        }

        foreach ($signs as $sign) {
            if ($sign instanceof Sign) {
                if (trim($sign->getText()->getLine(0)) === "§6[§eSubClaim§6]") {
                    $line1 = trim($sign->getText()->getLine(1));
                    if ($line1 === "'{$player->getName()}'" || $line1 === $player->getName()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function getAdjacentSigns($world, $pos): array
    {
        return [
            $world->getTileAt($pos->x + 1, $pos->y, $pos->z),
            $world->getTileAt($pos->x - 1, $pos->y, $pos->z),
            $world->getTileAt($pos->x, $pos->y, $pos->z + 1),
            $world->getTileAt($pos->x, $pos->y, $pos->z - 1),
        ];
    }
}