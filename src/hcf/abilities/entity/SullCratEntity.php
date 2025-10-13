<?php

namespace hcf\abilities\entity;

use hcf\HCFLoader;
use pocketmine\entity\projectile\Snowball;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HugeExplodeParticle;

class SullCratEntity extends Snowball
{
    public function onUpdate(int $currentTick): bool
    {
        $hasUpdate = parent::onUpdate($currentTick);

        if ($hasUpdate && $currentTick % 20 === 0) {
            $world = $this->getWorld();
            $pos = $this->getPosition();
            $world->addParticle($pos, new FlameParticle());
            $world->addParticle($pos, new HugeExplodeParticle());
        }
        return $hasUpdate;
    }
}