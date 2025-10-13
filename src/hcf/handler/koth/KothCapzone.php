<?php

declare(strict_types=1);

namespace hcf\handler\koth;

use pocketmine\world\Position;

class KothCapzone
{
    private int $minX;
    private int $maxX;
    private int $minY;
    private int $maxY;
    private int $minZ;
    private int $maxZ;
    private string $world;

    public function __construct(
        int $minX,
        int $maxX,
        int $minY,
        int $maxY,
        int $minZ,
        int $maxZ,
        string $world
    ) {
        $this->minX = $minX;
        $this->maxX = $maxX;
        $this->minY = $minY;
        $this->maxY = $maxY;
        $this->minZ = $minZ;
        $this->maxZ = $maxZ;
        $this->world = $world;
    }

    public function getMinX(): int { return $this->minX; }
    public function getMinY(): int { return $this->minY; }
    public function getMinZ(): int { return $this->minZ; }
    public function getMaxX(): int { return $this->maxX; }
    public function getMaxY(): int { return $this->maxY; }
    public function getMaxZ(): int { return $this->maxZ; }
    public function getWorld(): string { return $this->world; }

    public function inside(Position $position): bool
    {
        return $this->world === $position->getWorld()->getFolderName()
            && $this->minX <= $position->getFloorX() && $this->maxX >= $position->getFloorX()
            && $this->minY <= $position->getFloorY() && $this->maxY >= $position->getFloorY()
            && $this->minZ <= $position->getFloorZ() && $this->maxZ >= $position->getFloorZ();
    }
}