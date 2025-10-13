<?php

declare(strict_types=1);

namespace hcf\handler\koth\command\subcommand;

use hcf\handler\koth\command\KothSubCommand;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat;

class EditSubCommand implements KothSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize("&cUse this command in-game."));
            return;
        }

        $koths = HCFLoader::getInstance()->getKothManager()->getKoths();
        if (empty($koths)) {
            $sender->sendMessage(TextFormat::colorize("&cNo KOTHs to edit."));
            return;
        }

        $form = new SimpleForm(function (Player $player, ?int $data) use ($koths): void {
            if ($data === null) return;

            $kothNames = array_keys($koths);
            $selectedName = $kothNames[$data] ?? null;

            if ($selectedName === null) {
                $player->sendMessage(TextFormat::colorize("&cInvalid KOTH selection."));
                return;
            }

            $koth = $koths[$selectedName] ?? null;
            if ($koth === null) {
                $player->sendMessage(TextFormat::colorize("&cKOTH not found."));
                return;
            }

            $this->openEditForm($player, $koth);
        });

        $form->setTitle("Edit KOTH");
        foreach ($koths as $koth) {
            $form->addButton($koth->getName());
        }
        $sender->sendForm($form);
    }

    private function openEditForm(Player $player, $koth): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) use ($koth): void {
            if ($data === null) return;

            $delete = $data[4] ?? false;
            if ($delete === true) {
                HCFLoader::getInstance()->getKothManager()->removeKoth($koth->getName());
                $player->sendMessage(TextFormat::colorize("&aKOTH '{$koth->getName()}' deleted."));
                return;
            }

            $newName = trim((string)($data[0] ?? ""));
            $newPoints = filter_var($data[1] ?? 0, FILTER_VALIDATE_INT);
            $newTime = filter_var($data[2] ?? 0, FILTER_VALIDATE_INT);
            $newCoords = trim((string)($data[3] ?? ""));

            if ($newName === "" || $newPoints === false || $newPoints <= 0 || $newTime === false || $newTime <= 0) {
                $player->sendMessage(TextFormat::colorize("&cAll fields are required and must be valid."));
                return;
            }

            $manager = HCFLoader::getInstance()->getKothManager();

            if ($newName !== $koth->getName()) {
                if ($manager->getKoth($newName) !== null) {
                    $player->sendMessage(TextFormat::colorize("&cA KOTH with the name '{$newName}' already exists."));
                    return;
                }

                $manager->removeKoth($koth->getName());
                $manager->createKoth($newName, $newTime, $newPoints, $newCoords, null, null);
                $player->sendMessage(TextFormat::colorize("&aKOTH renamed to '{$newName}' and updated."));
            } else {
                $koth->setPoints($newPoints);
                $koth->setTime($newTime);
                $koth->setCoords($newCoords);
                $player->sendMessage(TextFormat::colorize("&aKOTH '{$koth->getName()}' updated."));
            }
        });

        $form->setTitle("Edit KOTH: " . $koth->getName());
        $form->addInput("KOTH Name:", "Name", $koth->getName());
        $form->addInput("Points (int):", "e.g. 10", (string)$koth->getPoints());
        $form->addInput("Duration (seconds):", "e.g. 300", (string)$koth->getTime());
        $form->addInput("Coords (optional):", "e.g. 100,64,100", $koth->getCoords() ?? "");
        $form->addToggle("§cDelete this KOTH", false);

        $player->sendForm($form);
    }
}