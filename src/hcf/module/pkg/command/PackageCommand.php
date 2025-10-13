<?php

namespace hcf\module\pkg\command;

use hcf\HCFLoader;
use hcf\module\pkg\util\Content;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PackageCommand extends Command {
    
    public function __construct() {
        parent::__construct('pkg', 'Use command to partner packages.');
        $this->setPermission('op.cmd');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&aUsa /ppackage give | giveall | edit'));
            return;
        }

        switch (strtolower($args[0])) {
            case 'give':
                if (count($args) < 3) {
                    $sender->sendMessage(TextFormat::colorize('&cUsa /ppackage give [jugador] [cantidad]'));
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                
                if (!$player instanceof Player) {
                    $sender->sendMessage(TextFormat::colorize('&cJugador inválido.'));
                    return;
                }
                
                if (!is_numeric($args[2])) {
                    $sender->sendMessage(TextFormat::colorize('&cLa cantidad no es válida.'));
                    return;
                }
                $count = (int)$args[2];
                $ppackageItem = Content::getInstance()->getPackage($count);
                
                if ($player->getInventory()->canAddItem($ppackageItem)) {
                    $player->getInventory()->addItem($ppackageItem);
                } else {
                    $player->dropItem($ppackageItem);
                }

                $legendPrefix = HCFLoader::$prefix;

                Server::getInstance()->broadcastMessage(
                    TextFormat::colorize("{$legendPrefix}&e{$sender->getName()} &aha dado &e{$count} &apackages a &e{$player->getName()}")
                );
                break;
                
            case 'giveall':
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUsa /ppackage giveall [cantidad]'));
                    return;
                }

                if (!is_numeric($args[1])) {
                    $sender->sendMessage(TextFormat::colorize('&cLa cantidad no es válida.'));
                    return;
                }
                $count = (int)$args[1];
                $ppackageItem = Content::getInstance()->getPackage($count);
                
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if ($player->getInventory()->canAddItem($ppackageItem)) {
                        $player->getInventory()->addItem($ppackageItem);
                    } else {
                        $player->dropItem($ppackageItem);
                    }
                }

                $legendPrefix = HCFLoader::$prefix;

                Server::getInstance()->broadcastMessage(
                    TextFormat::colorize("{$legendPrefix}&e{$sender->getName()} &aha dado &e{$count} &apackages a todos los jugadores en línea.")
                );
                break;
                
            case 'edit':
                if (!$sender instanceof Player) {
                    return;
                }
                Content::getInstance()->sendMenu($sender);
                break;
        }
    }
}