<?php

namespace hcf\module\modules;

use hcf\HCFLoader;
use pocketmine\event\Listener;

final class ModulesManager implements Listener {
    private static array $modules = [
        \hcf\module\modules\events\TransactionEnchants::class,
        \hcf\module\modules\events\Hojas::class,
        \hcf\module\modules\events\NoNight::class,
        
    ];

    public static function init(): void {
        $plugin = HCFLoader::getInstance();

        foreach (self::$modules as $module) {
            $instance = new $module($plugin);
            if ($instance instanceof Listener) {
                $plugin->getServer()->getPluginManager()->registerEvents($instance, $plugin);
            }
        }
    }
}
