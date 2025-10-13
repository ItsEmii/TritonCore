<?php

namespace hcf\module\modules\events;

use pocketmine\event\Listener;
use pocketmine\event\block\LeavesDecayEvent;

class Hojas implements Listener {

    public function onLeavesDecay(LeavesDecayEvent $event): void {
        $event->cancel(); 
    }
}
