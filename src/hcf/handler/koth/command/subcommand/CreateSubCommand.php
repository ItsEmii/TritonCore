<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\item\VanillaItems;

class CreateSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cThis command can only be used in-game.'));
            return;
        }

        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) return;

            $name = trim((string)($data[0] ?? ""));
            $points = intval($data[1] ?? 0);
            $duration = intval($data[2] ?? 0);

            $abilitiesOn = ($data[3] ?? 1) === 1; // Dropdown: 0=OFF, 1=ON
            $enderOn = ($data[4] ?? 1) === 1;     // Dropdown: 0=OFF, 1=ON
            $enderCooldown = intval($data[5] ?? 0);

            if ($enderOn) $enderCooldown = 0;

            if ($name === "" || $points <= 0 || $duration <= 0) {
                $player->sendMessage(TextFormat::colorize('&cAll fields must be valid and not empty.'));
                return;
            }

            $kothManager = HCFLoader::getInstance()->getKothManager();
            if ($kothManager->getKoth($name) !== null) {
                $player->sendMessage(TextFormat::colorize('&cThis KOTH already exists.'));
                return;
            }

            $kothManager->createKoth($name, $duration, $points, $abilitiesOn, $enderOn, $enderCooldown);
            $player->sendMessage(TextFormat::colorize("&aYou have successfully created KOTH '{$name}' with {$points} points and {$duration} seconds."));

            $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eClaim Selector'));
            $nbt = $item->getNamedTag();
            $nbt->setString('claim_type', 'koth');
            $item->setNamedTag($nbt);

            if ($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
                HCFLoader::getInstance()->getClaimManager()->createCreator($player->getName(), $name, 'koth');
                $player->sendMessage(TextFormat::colorize('&aYou can now select the claim area using the golden hoe.'));
            } else {
                $player->sendMessage(TextFormat::colorize('&cYou do not have enough inventory space for the claim selector.'));
            }
        });

        $form->setTitle("Create KOTH");
        $form->addInput("KOTH Name:", "Ex: MyKoth");
        $form->addInput("Points to give:", "Ex: 10");
        $form->addInput("Duration (seconds):", "Ex: 300");
        $form->addDropdown("Abilities ON/OFF in this KOTH?", ["OFF", "ON"], 1);
        $form->addDropdown("Ender Pearls ON/OFF in this KOTH?", ["OFF", "ON"], 1);
        $form->addInput("EnderPearl cooldown if OFF (seconds):", "Ex: 10", "10");

        $sender->sendForm($form);
    }
}