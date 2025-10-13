<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\utils\TextFormat;

class FixCommand extends Command
{
    public function __construct()
    {
        parent::__construct('fix', '§hComando para reparar items');
        $this->setPermission('fix.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede usarlo un jugador.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(
                $prefix .
                "§eComandos de fix:\n" .
                "§7/fix hand §e- Repara el item que tienes en la mano\n" .
                "§7/fix all §e- Repara todos los items en tu inventario y armadura\n" .
                "§7/fix all [jugador] §e- Repara todos los items del inventario y armadura de un jugador"
            );
            return;
        }

        switch (strtolower($args[0])) {
            case 'hand':
                $item = $sender->getInventory()->getItemInHand();

                if (!$item instanceof Durable) {
                    $sender->sendMessage($prefix . TextFormat::RED . "No tienes un item reparable en la mano.");
                    return;
                }

                if ($item->getDamage() === 0) {
                    $sender->sendMessage($prefix . TextFormat::RED . "El item ya está reparado.");
                    return;
                }

                $item->setDamage(0);
                $sender->getInventory()->setItemInHand($item);
                $sender->sendMessage($prefix . TextFormat::GREEN . "Has reparado el item en tu mano correctamente.");
                break;

            case 'all':
                if (count($args) < 2) {
                    foreach ($sender->getInventory()->getContents() as $slot => $item) {
                        if ($item instanceof Durable && $item->getDamage() > 0) {
                            $item->setDamage(0);
                            $sender->getInventory()->setItem($slot, $item);
                        }
                    }

                    foreach ($sender->getArmorInventory()->getContents() as $slot => $armor) {
                        if ($armor instanceof Armor && $armor->getDamage() > 0) {
                            $armor->setDamage(0);
                            $sender->getArmorInventory()->setItem($slot, $armor);
                        }
                    }

                    $sender->sendMessage($prefix . TextFormat::GREEN . "Has reparado todos tus items y armadura.");
                    return;
                }

                if (!$sender->hasPermission('fix.player.command')) {
                    $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para reparar items de otros jugadores.");
                    return;
                }

                $player = $sender->getServer()->getPlayerByPrefix($args[1]);

                if (!$player instanceof Player) {
                    $sender->sendMessage($prefix . TextFormat::RED . "El jugador no está en línea.");
                    return;
                }

                foreach ($player->getInventory()->getContents() as $slot => $item) {
                    if ($item instanceof Durable && $item->getDamage() > 0) {
                        $item->setDamage(0);
                        $player->getInventory()->setItem($slot, $item);
                    }
                }

                foreach ($player->getArmorInventory()->getContents() as $slot => $armor) {
                    if ($armor instanceof Armor && $armor->getDamage() > 0) {
                        $armor->setDamage(0);
                        $player->getArmorInventory()->setItem($slot, $armor);
                    }
                }

                $sender->sendMessage($prefix . TextFormat::GREEN . "Has reparado todos los items y armadura de " . $player->getName() . ".");
                $player->sendMessage($prefix . TextFormat::GREEN . "Alguien te ha reparado todos los items y armadura.");
                break;

            default:
                $sender->sendMessage(
                    $prefix .
                    "§eComandos de fix:\n" .
                    "§7/fix hand §e- Repara el item que tienes en la mano\n" .
                    "§7/fix all §e- Repara todos los items en tu inventario y armadura\n" .
                    "§7/fix all [jugador] §e- Repara todos los items del inventario y armadura de un jugador"
                );
                break;
        }
    }
}