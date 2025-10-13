<?php

namespace hcf\block;

use hcf\HCFLoader;
use hcf\block\listener\BlockInteractListener;
use hcf\block\listener\ItemBlockListener;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class BlockManager
{
    private HCFLoader $plugin;

    public function __construct()
    {
        $this->plugin = HCFLoader::getInstance();
    }

    public function register(): void
    {
        $pm = $this->plugin->getServer()->getPluginManager();


        $pm->registerEvents(new BlockInteractListener(), $this->plugin);
        $pm->registerEvents(new ItemBlockListener(), $this->plugin);
    }
}