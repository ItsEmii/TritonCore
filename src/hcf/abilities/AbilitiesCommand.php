<?php

declare(strict_types=1);

namespace hcf\abilities;

use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class AbilitiesCommand extends Command
{
    private string $shopLink = '§r§7Shop: store.legendsmc.fun';
    private string $prefix = "§r§7[§bLegends§fMC§7] §r";
    private EnchantmentInstance $infinityEnchantment;

    public function __construct()
    {
        parent::__construct(
            'abilities',
            '§hObtén los ítems de habilidades especiales',
            '/abilities',
            ['gb']
        );
        $this->setPermission("op.cmd");
        $this->infinityEnchantment = new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1);
    }

    private function createAbilityItem(Item $item, string $name, string $ability, array $extraLore = []): Item
    {
        $item->addEnchantment($this->infinityEnchantment);
        $item->setCustomName($name);
        $lore = [$this->shopLink];
        $lore = array_merge($lore, $extraLore);
        $item->setLore($lore);
        $item->getNamedTag()->setString("Abilities", $ability);
        return $item;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->prefix . "§cEste comando solo puede usarse en juego.");
            return false;
        }

        $inventory = $sender->getInventory();

        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::POISONOUS_POTATO(), "§hAbilityDisabler", "AbilityDisabler")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::SLIMEBALL(), "§hEffectDisabler", "EffectDisabler")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::BONE(), "§hExoticBone", "ExoticBone")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::EGG(), "§hBallOfRange", "BallOfRange")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::ZOMBIE_SPAWN_EGG(), "§hPortableBard", "PortableBard")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::BLAZE_POWDER(), "§hStrength 2", "Strength")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::IRON_INGOT(), "§hResistance 3", "Resistance")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::SNOWBALL(), "§hSwitcher", "Switcher")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::GLASS_BOTTLE(), "§hRefillPots", "Potion")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::SHEARS(), "§hNinjaShear", "NinjaShear")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::STONE_AXE(), "§hStromBreaker", "Strombreaker")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::MILK_BUCKET(), "§hTimeStone", "TimeStone")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::GOLD_NUGGET(), "§hRichBill", "RichBill")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::PAPER(), "§hMedKit", "MedKit")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::CLOCK(), "§hGuardianAngel", "GuardianAngel")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::BLAZE_ROD(), "§hFlowerTank", "FlowerTank")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::FIRE_CHARGE(), "§hSullCrat", "SullCrat")
        );
        $inventory->addItem(
            $this->createAbilityItem(VanillaItems::SPIDER_EYE(), "§hPortableMague", "PortableMague")
        );

        $sender->sendMessage($this->prefix . "§aHas recibido todos lo Abilities");

        return true;
    }

    public static function getItem(string|int $id, int $meta = 0, int $count = 1): Item
    {
        return LegacyStringToItemParser::getInstance()->parse("{$id}:{$meta}")->setCount($count);
    }
}