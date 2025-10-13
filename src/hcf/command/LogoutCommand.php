<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class LogoutCommand extends Command
{
    public function __construct()
    {
        parent::__construct('logout', '§hUse /logout to safely leave the server (e.g. /LOGOUT, /Logout)');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;

        if ($sender->getSession()->getCooldown('logout') !== null) return;

        $sender->getSession()->addCooldown('logout', '', 35);

        $xuid = (string)$sender->getUniqueId();
        $position = $sender->getPosition();

        $handler = null;

        $handler = HCFLoader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use (&$handler, $sender, $xuid, $position): void {
            $session = HCFLoader::getInstance()->getSessionManager()->getSession($xuid);
            if ($session === null) {
                $handler?->cancel();
                return;
            }

            if (!$sender->isOnline()) {
                $session->removeCooldown('logout');
                $handler?->cancel();
                return;
            }

            if ($position->distance($sender->getPosition()) > 3) {
                $session->removeCooldown('logout');
                $sender->sendMessage("§e[§bTritonMC§e]  §h&cLogout cancelled: you moved.");
                $handler?->cancel();
                return;
            }

            if ($session->getCooldown('logout') === null) {
                $session->setLogout(true);
                $sender->kick("§e[§bTritonMC§e] §h&cYou have successfully logged out.");
                $handler?->cancel();
            }
        }), 20);
    }
}