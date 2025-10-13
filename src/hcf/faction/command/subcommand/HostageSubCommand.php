<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\Position;

class HostageSubCommand implements FactionSubCommand
{
    private function teleport(Player $player): void
    {
        $world = $player->getWorld();
        $x = mt_rand($player->getPosition()->getFloorX() - 100, $player->getPosition()->getFloorX() + 100);
        $z = mt_rand($player->getPosition()->getFloorZ() - 100, $player->getPosition()->getFloorZ() + 100);
        $y = $world->getHighestBlockAt($x, $z);

        $position = new Position($x, $y, $z, $world);

        if (HCFLoader::getInstance()->getClaimManager()->insideClaim($position) !== null) {
            $this->teleport($player);
            return;
        }

        $player->teleport($position->add(0, 1, 0));
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if ($sender->getSession()->getCooldown('faction.hostage') !== null) return;

        $sender->getSession()->addCooldown('faction.hostage', ' §9Hostage: ', 180);

        $xuid = (string)$sender->getUniqueId();
        $origin = $sender->getPosition();

        $handler = null;
        $handler = HCFLoader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (&$handler, $sender, $xuid, $origin): void {
            $session = HCFLoader::getInstance()->getSessionManager()->getSession($xuid);

            if (!$sender->isOnline()) {
                $session->removeCooldown('faction.hostage');
                $handler->cancel();
                return;
            }

            if ($origin->distance($sender->getPosition()) > 5) {
                $session->removeCooldown('faction.hostage');
                $handler->cancel();
                return;
            }

            if ($session->getCooldown('faction.hostage') === null) {
                (new HostageSubCommand())->teleport($sender);
                $handler->cancel();
            }
        }), 20);
    }
}