<?php

declare(strict_types=1);

namespace hcf\handler\kit\command\subcommand;

use hcf\handler\kit\command\KitSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\time\Timer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

class CreateSubCommand implements KitSubCommand
{
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        $this->openMainForm($sender);
    }

    private function openMainForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;

            $kitName = trim((string)$data[0]);
            $nameFormat = (string)$data[1];
            $cooldownInput = (string)$data[2];
            $permission = (string)$data[3];

            if ($kitName === "" || $nameFormat === "" || $cooldownInput === "" || $permission === "") {
                $player->sendMessage("§cAll fields are required.");
                return;
            }

            $cooldown = Timer::time($cooldownInput);
            if ($cooldown === -1) {
                $player->sendMessage("§cInvalid cooldown format.");
                return;
            }

            $kitManager = HCFLoader::getInstance()->getHandlerManager()->getKitManager();
            if ($kitManager->getKit($kitName) !== null) {
                $player->sendMessage("§cA kit with that name already exists.");
                return;
            }

            $data = [
                "kitName" => $kitName,
                "nameFormat" => $nameFormat,
                "cooldown" => $cooldown,
                "permission" => $permission,
                "item" => $player->getInventory()->getItemInHand(),
                "items" => $player->getInventory()->getContents(),
                "armor" => $player->getArmorInventory()->getContents(),
            ];

            $this->openCategoryForm($player, $data);
        });

        $form->setTitle("§l§eCreate Kit");
        $form->addInput("Kit Name", "archer");
        $form->addInput("Name Format", "§bArcher");
        $form->addInput("Cooldown (1d, 3h, 30m)", "e.g., 1h");
        $form->addInput("Permission", "archer.kit");

        $player->sendForm($form);
    }

    private function openCategoryForm(Player $player, array $kitData): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data = null) use ($kitData) {
            if ($data === null) return;

            $category = match ($data) {
                0 => "free",
                1 => "pay",
                2 => "legendary",
                default => null
            };

            if ($category === null) {
                $player->sendMessage("§cInvalid category.");
                return;
            }

            $handler = HCFLoader::getInstance()->getHandlerManager();

            switch ($category) {
                case "free":
                    $handler->getKitManager()->addKit($kitData["kitName"], $kitData["nameFormat"], $kitData["permission"], $kitData["item"], $kitData["items"], $kitData["armor"], $kitData["cooldown"]);
                    break;
                case "pay":
                    $handler->getKitPayManager()->addKit($kitData["kitName"], $kitData["nameFormat"], $kitData["permission"], $kitData["item"], $kitData["items"], $kitData["armor"], $kitData["cooldown"]);
                    break;
                case "legendary":
                    $handler->getKitLManager()->addKit($kitData["kitName"], $kitData["nameFormat"], $kitData["permission"], $kitData["item"], $kitData["items"], $kitData["armor"], $kitData["cooldown"]);
                    break;
            }

            $player->sendMessage("§aKit §f" . $kitData["kitName"] . " §acreated successfully!");
            HCFLoader::getInstance()->getHandlerManager()->getKitManager()->registerPermission($kitData["permission"]);
        });

        $form->setTitle("§l§eKit Category");
        $form->setContent("Select the kit category:");
        $form->addButton("§aFree");
        $form->addButton("§ePay");
        $form->addButton("§6Legendary");

        $player->sendForm($form);
    }
}