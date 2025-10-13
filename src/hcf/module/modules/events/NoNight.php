<?php

namespace hcf\module\modules\events;

use pocketmine\event\Listener;
use pocketmine\scheduler\ClosureTask;
use pocketmine\plugin\Plugin;

class NoNight implements Listener {

    private Plugin $plugin;

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;

        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
            if ($world->getTime() >= 13000) {
                $world->setTime(0);
            }
        }), 100);
    }
}
