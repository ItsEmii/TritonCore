<?php

namespace hcf\addons;

use hcf\HCFLoader;
use hcf\addons\modules\{
    NoHitSound,
    JoinCommand,
    SwordStats,
    OreBreak,
    EnderPearlDamage
};
use pocketmine\event\Listener;

final class AddonsManager implements Listener
{
    public static function init(): void
    {
        $plugin = HCFLoader::getInstance();
        $manager = $plugin->getServer()->getPluginManager();

        $manager->registerEvents(new NoHitSound(), $plugin);
        $manager->registerEvents(new JoinCommand(), $plugin);
        $manager->registerEvents(new SwordStats(), $plugin);
        //$manager->registerEvents(new TemporalCobwebs(), $plugin);
        $manager->registerEvents(new OreBreak(), $plugin);
        $manager->registerEvents(new EnderPearlDamage(), $plugin);
    }
}