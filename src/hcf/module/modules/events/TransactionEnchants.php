<?php

namespace hcf\module\modules\events;

use yeivwi\ce\enchantments\CustomEnchant;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\FlintSteel;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ItemBreakSound;
use hcf\utils\Utils;

final class TransactionEnchants implements Listener {

    private const FLAG_HOLDABLE = ItemFlags::SWORD | ItemFlags::BOW | ItemFlags::TOOL | ItemFlags::DIG | ItemFlags::FISHING_ROD | ItemFlags::CARROT_STICK;

    private const ARMOR_SLOT_TO_ITEMFLAG = [
        ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
        ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
        ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
        ArmorInventory::SLOT_FEET => ItemFlags::FEET,
    ];

    private const TOOL_TO_ITEMFLAG = [
        Pickaxe::class => ItemFlags::PICKAXE,
        Sword::class => ItemFlags::SWORD,
        Axe::class => ItemFlags::AXE,
        Hoe::class => ItemFlags::HOE,
        Shovel::class => ItemFlags::SHOVEL,
        Bow::class => ItemFlags::BOW,
        FlintSteel::class => ItemFlags::FLINT_AND_STEEL,
        Shears::class => ItemFlags::SHEARS,
    ];

    public function onTransaction(InventoryTransactionEvent $event): void {
        $player = $event->getTransaction()->getSource();
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());

        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                if ($action instanceof SlotChangeAction &&
                    ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction &&
                    ($itemClickedWith = $action->getTargetItem())->getTypeId() === ItemTypeIds::ENCHANTED_BOOK &&
                    ($itemClicked = $action->getSourceItem())->getTypeId() !== BlockTypeIds::AIR) {

                    if ($itemClicked instanceof Armor || $itemClicked instanceof Tool) {
                        if (count($itemClickedWith->getEnchantments()) < 1) return;
                        $enchantmentSuccessful = false;

                        foreach ($itemClickedWith->getEnchantments() as $enchantment) {
                            $type = $enchantment->getType();
                            $currentLevel = $enchantment->getLevel();
                            $maxLevel = $type->getMaxLevel();
                            $enchantmentType = $enchantment->getType();

                            
                            if (($existingEnchant = $itemClicked->getEnchantment($enchantment->getType())) !== null) {
                                
                                if ($existingEnchant->getLevel() >= $currentLevel) {
                                    continue;
                                }

                                
                                if ($existingEnchant->getLevel() + 1 > $maxLevel) {
                                    
                                    $player->sendMessage("§c» §6Este encantamiento ha alcanzado el límite máximo: " . $type->getName());
                                    $player->getWorld()->addSound($player->getPosition(), new ItemBreakSound());
                                    $event->cancel();
                                    return;
                                }

                                
                                $currentLevel = $existingEnchant->getLevel() + 1;
                            }

                            
                            $itemClicked->addEnchantment(new EnchantmentInstance($type, min($currentLevel, $maxLevel)));
                            $lore = $this->generateLoreLines($itemClicked);
                            $itemClicked->setLore($lore);
                            $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $enchantmentSuccessful = true;
                        }

                        if ($enchantmentSuccessful) {
                            $event->cancel();
                            $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());

                            
                            if ($action->getInventory() instanceof PlayerInventory) {
                                $holder = $action->getInventory()->getHolder();
                                if ($holder instanceof Player) {
                                    $holder->getXpManager()->addXp(1000);
                                    $holder->getXpManager()->subtractXp(1000);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function generateLoreLines(Item $item): array {
        $lore = [];
        foreach ($item->getEnchantments() as $ench) {
            $type = $ench->getType();
            if (!$type instanceof CustomEnchant) continue;
            $lore[] = TextFormat::RESET . $type->getLoreLine($ench->getLevel());
        }
        return array_unique($lore);
    }
}