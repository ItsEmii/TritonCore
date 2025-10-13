<?php

namespace hcf\module;

use hcf\HCFLoader;
use hcf\module\blockshop\BlockShopManager;
use hcf\module\coinshop\CoinShopManager;

class ModuleManager {

    
    public BlockShopManager $blockshopManager;
    public CoinShopManager $coinShopManager;
    
    
    public function __construct(){
        
        $this->blockshopManager = new BlockShopManager();
        $this->coinShopManager = new CoinShopManager;
        
    }
   
    
    public function getBlockShopManager(): BlockShopManager {
        return $this->blockshopManager;
    }
    
    public function getCoinShopManager(): CoinShopManager {
        return $this->coinShopManager;
    }
    
}