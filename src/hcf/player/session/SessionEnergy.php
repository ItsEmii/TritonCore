<?php

declare(strict_types=1);

namespace hcf\player\session;

class SessionEnergy
{
    
    private string $format;
    private int $energy;
    private bool $paused;

    public function __construct(string $format, int $energy, bool $paused)
    {
        $this->format = $format;
        $this->energy = $energy;
        $this->paused = $paused;
    }
    
    public function getFormat(): string
    {
        return $this->format;
    }
    
    public function getEnergy(): int
    {
        return $this->energy;
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function setPaused(bool $value): void
    {
        $this->paused = $value;
    }
    
    public function addEnergy(int $amount): void
    {
        $this->energy += $amount;
    }
    
    public function reduceEnergy(int $amount): void
    {
        $this->energy -= $amount;
    }

    public function update(): void
    {
            $this->energy++;
    }
}