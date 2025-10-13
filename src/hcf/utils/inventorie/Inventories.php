<?php

declare(strict_types=1);

namespace hcf\utils\inventorie;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\item\Items;
use hcf\utils\time\Timer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\inventory\Inventory;
use pocketmine\utils\TextFormat;

final class Inventories
{
    public static function createKitOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $organization = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getOrganization();

        for ($i = 0; $i < 54; $i++) {
            if (isset($organization[$i])) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($organization[$i]);
                if ($kit !== null) {
                    $menu->getInventory()->setItem($i, Items::createItemKitOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
                } else {
                    $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
                }
            } else {
                $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('kit_name') !== null) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);

                if ($kit !== null) {
                    if ($kit->getPermission() !== null && !$player->hasPermission($kit->getPermission())) {
                        $player->sendMessage(TextFormat::colorize('&cNo tienes permiso para usar este kit'));
                        return $transaction->discard();
                    }

                    $cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName());
                    if ($cooldown !== null) {
                        $remaining = Timer::getTimeToString($cooldown->getTime());
                        $player->sendMessage(TextFormat::colorize('&cTienes cooldown en el kit. Tiempo restante: ' . $remaining));
                        return $transaction->discard();
                    }

                    $kit->giveTo($player);

                    if ($kit->getCooldown() !== 0) {
                        $player->getSession()->addCooldown('kit.' . $kit->getName(), '', $kit->getCooldown(), false, false);
                    }

                    $player->removeCurrentWindow();
                }
            }
            return $transaction->discard();
        });

        $menu->send($player, TextFormat::colorize('&eKits Free'));
    }

    public static function createKitLOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $organization = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getOrganization();

        for ($i = 0; $i < 54; $i++) {
            if (isset($organization[$i])) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($organization[$i]);
                if ($kit !== null) {
                    $menu->getInventory()->setItem($i, Items::createItemKitLOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
                } else {
                    $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
                }
            } else {
                $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('kit_name') !== null) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($kitName);

                if ($kit !== null) {
                    if ($kit->getPermission() !== null && !$player->hasPermission($kit->getPermission())) {
                        $player->sendMessage(TextFormat::colorize('&cNo tienes permiso para usar este kit'));
                        return $transaction->discard();
                    }

                    $cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName());
                    if ($cooldown !== null) {
                        $remaining = Timer::getTimeToString($cooldown->getTime());
                        $player->sendMessage(TextFormat::colorize('&cTienes cooldown en el kit. Tiempo restante: ' . $remaining));
                        return $transaction->discard();
                    }

                    $kit->giveTo($player);

                    if ($kit->getCooldown() !== 0) {
                        $player->getSession()->addCooldown('kit.' . $kit->getName(), '', $kit->getCooldown(), false, false);
                    }

                    $player->removeCurrentWindow();
                }
            }
            return $transaction->discard();
        });

        $menu->send($player, TextFormat::colorize('§r§eKits Legendary'));
    }

    public static function createKitPayOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $organization = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getOrganization();

        for ($i = 0; $i < 54; $i++) {
            if (isset($organization[$i])) {
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($organization[$i]);
                if ($kit !== null) {
                    $menu->getInventory()->setItem($i, Items::createItemKitPayOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
                } else {
                    $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
                }
            } else {
                $menu->getInventory()->setItem($i, VanillaBlocks::AIR()->asItem());
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('kit_name') !== null) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($kitName);

                if ($kit !== null) {
                    if ($kit->getPermission() !== null && !$player->hasPermission($kit->getPermission())) {
                        $player->sendMessage(TextFormat::colorize('&cNo tienes permiso para usar este kit'));
                        return $transaction->discard();
                    }

                    $cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName());
                    if ($cooldown !== null) {
                        $remaining = Timer::getTimeToString($cooldown->getTime());
                        $player->sendMessage(TextFormat::colorize('&cTienes cooldown en el kit. Tiempo restante: ' . $remaining));
                        return $transaction->discard();
                    }

                    $kit->giveTo($player);

                    if ($kit->getCooldown() !== 0) {
                        $player->getSession()->addCooldown('kit.' . $kit->getName(), '', $kit->getCooldown(), false, false);
                    }

                    $player->removeCurrentWindow();
                }
            }
            return $transaction->discard();
        });

        $menu->send($player, TextFormat::colorize('§bKits Pay'));
    }

    public static function createMenuKit(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->getInventory()->setItem(11, VanillaItems::IRON_SWORD()->setCustomName(TextFormat::colorize("§r§fFree §bKits")));
        $menu->getInventory()->setItem(13, VanillaItems::GOLDEN_SWORD()->setCustomName(TextFormat::colorize("§r§eLegendary §bKits")));
        $menu->getInventory()->setItem(15, VanillaItems::DIAMOND_SWORD()->setCustomName(TextFormat::colorize("§r§bPay §bKits")));

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();

            if ($item->getCustomName() === TextFormat::colorize("§r§fFree §bKits")) {
                self::createKitOrganization($player);
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            if ($item->getCustomName() === TextFormat::colorize("§r§eLegendary §bKits")) {
                self::createKitLOrganization($player);
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            if ($item->getCustomName() === TextFormat::colorize("§r§bPay §bKits")) {
                self::createKitPayOrganization($player);
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            return $transaction->discard();
        });

        $menu->send($player, TextFormat::colorize('§r&eKits Menu'));
    }

    public static function editKitOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);

        foreach (HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getOrganization() as $slot => $kitName) {
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);
            if ($kit !== null) {
                $menu->getInventory()->setItem($slot, Items::createItemKitOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClickedWith();

            if (!$item->isNull() && $item->getNamedTag()->getTag('kit_name') === null) {
                return $transaction->discard();
            }
            return $transaction->continue();
        });

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $data = [];
            $contents = $inventory->getContents();

            foreach ($contents as $slot => $item) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);

                if ($kit !== null) {
                    $data[$slot] = $kit->getName();
                }
            }

            HCFLoader::getInstance()->getHandlerManager()->getKitManager()->setOrganization($data);
        });

        $menu->send($player, TextFormat::colorize('&6Edit kit organization'));
    }

    public static function editKitLOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);

        foreach (HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getOrganization() as $slot => $kitName) {
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($kitName);
            if ($kit !== null) {
                $menu->getInventory()->setItem($slot, Items::createItemKitLOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClickedWith();

            if (!$item->isNull() && $item->getNamedTag()->getTag('kit_name') === null) {
                return $transaction->discard();
            }
            return $transaction->continue();
        });

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $data = [];
            $contents = $inventory->getContents();

            foreach ($contents as $slot => $item) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($kitName);

                if ($kit !== null) {
                    $data[$slot] = $kit->getName();
                }
            }

            HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->setOrganization($data);
        });

        $menu->send($player, TextFormat::colorize('&6Edit kit organization'));
    }

    public static function editKitPayOrganization(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);

        foreach (HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getOrganization() as $slot => $kitName) {
            $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($kitName);
            if ($kit !== null) {
                $menu->getInventory()->setItem($slot, Items::createItemKitPayOrganization($player, $kit->getRepresentativeItem(), $kit->getName()));
            }
        }

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            /** @var Player $player */
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClickedWith();

            if (!$item->isNull() && $item->getNamedTag()->getTag('kit_name') === null) {
                return $transaction->discard();
            }
            return $transaction->continue();
        });

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $data = [];
            $contents = $inventory->getContents();

            foreach ($contents as $slot => $item) {
                $kitName = $item->getNamedTag()->getString('kit_name');
                $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($kitName);

                if ($kit !== null) {
                    $data[$slot] = $kit->getName();
                }
            }

            HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->setOrganization($data);
        });

        $menu->send($player, TextFormat::colorize('&6Edit kit organization'));
    }
}