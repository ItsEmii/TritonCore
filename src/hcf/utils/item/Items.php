<?php

declare(strict_types=1);

namespace hcf\utils\item;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\time\Timer;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

/**
 * Class Items
 * @package hcf\utils
 */
final class Items
{
    /** @var string[] */
    private static $kitLore = [
        '§r§eStore : store.legendsmc.fun',
        '&r',
        '§r&eCooldown: &f{kit_cooldown}',
        '§r&eDisponibles en: &f{player_cooldown}'
    ];

/*public static function createItemKitOrganization(Player $player, Item $item, string $kitName): Item
    {
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);

        if ($kit->getPermission() !== null) {
            if (!$player->hasPermission($kit->getPermission())) {
                if (!HCFLoader::getInstance()->getTimerManager()->getFreeKits()->isActive()) {
                    $item = VanillaBlocks::BARRIER()->asItem();
                }
            }
        }
    }*/
    /**
     * @param Player $player
     * @param Item $item
     * @param string $kitName
     * @return Item
     */
    public static function createItemKitOrganization(Player $player, Item $item, string $kitName): Item
    {
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitManager()->getKit($kitName);

        $item->setCustomName(TextFormat::colorize($kit->getNameFormat()));
        $item->setLore(array_map(function ($text) use ($player, $kit) {
            $player_cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName()) !== null ? Timer::getTimeToString($player->getSession()->getCooldown('kit.' . $kit->getName())->getTime()) : 'N/A';
            $kit_cooldown = $kit->getCooldown() !== 0 ? Timer::getTimeToString($kit->getCooldown()) : 'N/A';
            $text = str_replace(['{player_cooldown}', '{kit_cooldown}'], [$player_cooldown, $kit_cooldown], $text);
            return TextFormat::colorize($text);
        }, self::$kitLore));

        $namedtag = $item->getNamedTag();
        $namedtag->setString('kit_name', $kitName);
        $item->setNamedTag($namedtag);

        return $item;
    }

    public static function createItemKitPayOrganization(Player $player, Item $item, string $kitName): Item
    {
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitPayManager()->getKit($kitName);

        $item->setCustomName(TextFormat::colorize($kit->getNameFormat()));
        $item->setLore(array_map(function ($text) use ($player, $kit) {
            $player_cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName()) !== null ? Timer::getTimeToString($player->getSession()->getCooldown('kit.' . $kit->getName())->getTime()) : 'N/A';
            $kit_cooldown = $kit->getCooldown() !== 0 ? Timer::getTimeToString($kit->getCooldown()) : 'N/A';
            $text = str_replace(['{player_cooldown}', '{kit_cooldown}'], [$player_cooldown, $kit_cooldown], $text);
            return TextFormat::colorize($text);
        }, self::$kitLore));

        $namedtag = $item->getNamedTag();
        $namedtag->setString('kit_name', $kitName);
        $item->setNamedTag($namedtag);

        return $item;
    }
    
    public static function createItemKitLOrganization(Player $player, Item $item, string $kitName): Item
    {
        $kit = HCFLoader::getInstance()->getHandlerManager()->getKitLManager()->getKit($kitName);

        $item->setCustomName(TextFormat::colorize($kit->getNameFormat()));
        $item->setLore(array_map(function ($text) use ($player, $kit) {
            $player_cooldown = $player->getSession()->getCooldown('kit.' . $kit->getName()) !== null ? Timer::getTimeToString($player->getSession()->getCooldown('kit.' . $kit->getName())->getTime()) : 'N/A';
            $kit_cooldown = $kit->getCooldown() !== 0 ? Timer::getTimeToString($kit->getCooldown()) : 'N/A';
            $text = str_replace(['{player_cooldown}', '{kit_cooldown}'], [$player_cooldown, $kit_cooldown], $text);
            return TextFormat::colorize($text);
        }, self::$kitLore));

        $namedtag = $item->getNamedTag();
        $namedtag->setString('kit_name', $kitName);
        $item->setNamedTag($namedtag);

        return $item;
    }
}
