<?php

namespace hcf\faction\command\subcommand;

use hcf\faction\Faction;
use hcf\faction\FactionManager;
use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class CampSubCommand implements FactionSubCommand
{

    public function execute(CommandSender $sender, array $args) : void {

        if (!$sender instanceof Player) {
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage("§cUso: /f camp <faction>");
            return;
        }

        $factionName = $args[0];

        $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($factionName);

        if ($faction === null) {
            $sender->sendMessage("§cLa faction §e$factionName §cno existe!");
            return;
        }

        $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($factionName);

        if ($claim === null) {
            $sender->sendMessage("§cLa faction §e$factionName §cno tiene claim!");
            return;
        }

        if ($sender->getSession()->getCooldown('camp.tag') !== null)
            return;

        $sender->getSession()->addCooldown('camp.tag',  ' &1Camp&r&7: &c', HCFLoader::getInstance()->getConfig()->get('camp.timer'));

        $x = $claim->getMinX();
        $z = $claim->getMinZ();

        $world = Server::getInstance()->getWorldManager()->getWorldByName("world");
        $y = $world->getHighestBlockAt($x, $z);

        $pos = new Position($x, $y + 1, $z, $world);

        $xuid = (string)$sender->getUniqueId();
        $position = $sender->getPosition();
        $handler = null;
        $handler = HCFLoader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (&$handler, &$sender, &$xuid, &$position, &$pos, $factionName): void {
            $s = HCFLoader::getInstance()->getSessionManager()->getSession($xuid);

            if (!$sender->isOnline()) {
                if ($s->getCooldown('camp.tag') !== null) $s->removeCooldown('camp.tag');
                $handler->cancel();
                return;
            }

            if ($s->getCooldown('spawn.tag') !== null) {
                $s->removeCooldown('camp.tag');
                $handler->cancel();
                return;
            }

            if ($position->distance($sender->getPosition()) > 2) {
                if ($s->getCooldown('camp.tag') !== null) $s->removeCooldown('camp.tag');
                $handler->cancel();
                return;
            }

            if ($sender->getSession()->getCooldown('camp.tag') === null) {
                $sender->teleport($pos);
                $sender->sendMessage("§aHaz sido teletransportado cerca del claim §e" . $factionName . "§a!");
                $handler->cancel();
            }
        }), 20);
    }
}