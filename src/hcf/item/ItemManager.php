<?php

declare(strict_types=1);

namespace hcf\item;

use hcf\item\EnderpearlItem;
use hcf\HCFLoader;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class ItemManager
{

    /**
     * ItemManager construct.
     */
    public function __construct()
    {
        $itemDeserializer = GlobalItemDataHandlers::getDeserializer();
        $itemSerializer = GlobalItemDataHandlers::getSerializer();
        $stringToItemParser = StringToItemParser::getInstance();

        
        
        ItemManager::registerOnAllThreads();
    }

    public function onEnable(): void
    {
    }

    public static function registerOnAllThreads(): void
    {
        $pool = HCFLoader::getInstance()->getServer()->getAsyncPool();
        self::registerOnCurrentThread();
        $pool->addWorkerStartHook(function (int $worker) use ($pool): void {
            $pool->submitTaskToWorker(new class extends AsyncTask {
                public function onRun(): void
                {
                    ItemManager::registerOnCurrentThread();
                }
            }, $worker);
        });
    }

    public static function registerOnCurrentThread(): void
    {
        self::registerItems();
    }

    private static function registerItems(): void
    {

        self::registerItem(ItemTypeNames::ENDER_PEARL, new EnderpearlItem(new ItemIdentifier(ItemTypeIds::ENDER_PEARL, 0), 'Ender Pearl'), ['ender_pearl']);
        
    }

    private static function registerItem(string $id, Item $item, array $stringToItemParserNames, ?\Closure $serializerCallback = null, ?\Closure $deserializerCallback = null): void
    {
        $serializer = GlobalItemDataHandlers::getSerializer();
        $deserializer = GlobalItemDataHandlers::getDeserializer();

        (function () use ($id, $item, $serializerCallback): void {
            $this->itemSerializers[$item->getTypeId()] = $serializerCallback !== null ? $serializerCallback : static fn () => new SavedItemData($id);
        })->call($serializer);

        (function () use ($id, $item, $deserializerCallback): void {
            if (isset($this->deserializers[$id])) {
                unset($this->deserializers[$id]);
            }
            $this->map($id, $deserializerCallback !== null ? $deserializerCallback : static fn (SavedItemData $_) => clone $item);
        })->call($deserializer);

        foreach ($stringToItemParserNames as $name) {
            StringToItemParser::getInstance()->override($name, fn () => clone $item);
        }
    }
}