<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class ClaimForSubCommand implements FactionSubCommand
{
    /** @var string[] */
    private array $claims = [
        'Spawn' => 'spawn',
        'North Road' => 'road',
        'South Road' => 'road',
        'West Road' => 'road',
        'East Road' => 'road',
        'Events' => 'claim',
        'Citadel' => 'citadel',
        'TreasureIsland' => 'treasureisland',
    ];

    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (!$sender->hasPermission('faction.command.claimfor')) {
            $sender->sendMessage(TextFormat::colorize('&cNo tienes permiso para usar este comando.'));
            return;
        }

        if (count($args) < 1) {
            $this->openClaimMenu($sender);
            return;
        }

        $claimName = implode(' ', $args);

        if ($claimName === 'cancel') {
            if (($creator = HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName())) !== null && $creator->getType() === $this->claims[$creator->getName()]) {
                $creator->deleteCorners($sender);
                HCFLoader::getInstance()->getClaimManager()->removeCreator($sender->getName());
                $sender->sendMessage(TextFormat::colorize('&cHas cancelado el claim'));
            } else
                $sender->sendMessage(TextFormat::colorize('&cAún no estás en modo claim'));
            return;
        }

        if (HCFLoader::getInstance()->getClaimManager()->getCreator($sender->getName()) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa estás creando un claim'));
            return;
        }

        if (!isset($this->claims[$claimName])) {
            $claimType = 'custom';
        } else {
            $claimType = $this->claims[$claimName];
        }

        if (HCFLoader::getInstance()->getFactionManager()->getFaction($claimName) === null)
            HCFLoader::getInstance()->getFactionManager()->createFaction($claimName, [
                'roles' => [],
                'dtr' => 1.01,
                'balance' => 0,
                'points' => 0,
                'kothCaptures' => 0,
                'timeRegeneration' => null,
                'home' => null,
                'claim' => null
            ]);
        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eSelector de claim'));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', $claimType));

        if (!$sender->getInventory()->canAddItem($item)) {
            $sender->sendMessage(TextFormat::colorize('&cNo puedes añadir el item para hacer el claim en tu inventario'));
            return;
        }
        $sender->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($sender->getName(), $claimName, $claimType);
        $sender->sendMessage(TextFormat::colorize('&aAhora puedes reclamar el área'));
    }

    private function openClaimMenu(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->giveClaimHoe($player, "South Road", "road");
                    break;
                case 1:
                    $this->giveClaimHoe($player, "North Road", "road");
                    break;
                case 2:
                    $this->giveClaimHoe($player, "East Road", "road");
                    break;
                case 3:
                    $this->giveClaimHoe($player, "West Road", "road");
                    break;
                case 4:
                    $this->giveClaimHoe($player, "Spawn", "spawn");
                    break;
                case 5:
                    $this->giveClaimHoe($player, "TreasureIsland", "treasureisland");
                    break;
                case 6:
                    $this->openCustomClaimForm($player);
                    break;
            }
        });
        $form->setTitle("§l§6Selecciona tipo de claim");
        $form->addButton("South Road");
        $form->addButton("North Road");
        $form->addButton("East Road");
        $form->addButton("West Road");
        $form->addButton("Spawn");
        $form->addButton("TreasureIsland");
        $form->addButton("Custom");
        $player->sendForm($form);
    }

    private function openCustomClaimForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) return;
            $claimName = trim((string)($data[0] ?? ""));
            if ($claimName === "") {
                $player->sendMessage("§cDebes ingresar un nombre para el claim.");
                return;
            }
            $this->giveClaimHoe($player, $claimName, "custom");
        });
        $form->setTitle("Nombre personalizado para claim");
        $form->addInput("Ingresa el nombre del claim:");
        $player->sendForm($form);
    }

    private function giveClaimHoe(Player $player, string $claimName, string $claimType): void
    {
        if (HCFLoader::getInstance()->getClaimManager()->getCreator($player->getName()) !== null) {
            $player->sendMessage(TextFormat::colorize('&cYa estás creando un claim'));
            return;
        }

        if (HCFLoader::getInstance()->getFactionManager()->getFaction($claimName) === null)
            HCFLoader::getInstance()->getFactionManager()->createFaction($claimName, [
                'roles' => [],
                'dtr' => 1.01,
                'balance' => 0,
                'points' => 0,
                'kothCaptures' => 0,
                'timeRegeneration' => null,
                'home' => null,
                'claim' => null
            ]);
        $item = VanillaItems::GOLDEN_HOE()->setCustomName(TextFormat::colorize('&eSelector de claim: &f' . $claimName));
        $item->setNamedTag($item->getNamedTag()->setString('claim_type', $claimType));

        if (!$player->getInventory()->canAddItem($item)) {
            $player->sendMessage(TextFormat::colorize('&cNo puedes añadir el item para hacer el claim en tu inventario'));
            return;
        }
        $player->getInventory()->addItem($item);
        HCFLoader::getInstance()->getClaimManager()->createCreator($player->getName(), $claimName, $claimType);
        $player->sendMessage(TextFormat::colorize('&aAhora puedes reclamar el área para &e' . $claimName));
    }
}