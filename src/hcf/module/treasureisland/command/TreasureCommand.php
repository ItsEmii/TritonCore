<?php

namespace hcf\module\treasureisland\command;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class TreasureCommand extends Command {

    public function __construct() {
        parent::__construct('treasure', 'Treasure Island commands');
        $this->setPermission('op.cmd');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $manager = HCFLoader::getInstance()->getModuleManager()->getTreasureIslandManager();

        if (count($args) === 0) {
            $sender->sendMessage(
                TextFormat::GRAY . ("----------------------------------------------------------\n") .
                TextFormat::YELLOW . "/treasure - " . TextFormat::WHITE . "View help\n" .
                TextFormat::YELLOW . "/treasure update - " . TextFormat::WHITE . "Force refill chests\n" .
                TextFormat::YELLOW . "/treasure editcontent - " . TextFormat::WHITE . "Save your inventory as treasure items\n" .
                TextFormat::GRAY . ("----------------------------------------------------------")
            );
            return;
        }

        switch ($args[0]) {
            case "update":
                if (!$sender->getServer()->isOp($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission.");
                    return;
                }
                $manager->forceUpdateWithEffects($sender->getName());
                break;

            case "editcontent":
                if (!$sender->getServer()->isOp($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission.");
                    return;
                }

                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Only in-game players can use this.");
                    return;
                }

                $contents = array_filter($sender->getInventory()->getContents(), fn($item) => !$item->isNull());
                $count = count($contents);

                if ($count === 0) {
                    $sender->sendMessage(TextFormat::RED . "Your inventory is empty.");
                    return;
                }

                $manager->setItems($contents);
                $sender->sendMessage(TextFormat::GREEN . "Successfully saved §a{$count}§r items to TreasureIsland.");
                break;
        }
    }
}