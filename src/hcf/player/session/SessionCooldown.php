<?php

declare(strict_types=1);

namespace hcf\player\session;

class SessionCooldown
{
    
    private string $format;
    private int $time;
    private bool $paused;
    private bool $visible;
    
    public function __construct(string $format, int $time, bool $paused, bool $visible)
    {
        $this->format = $format;
        $this->time = $time;
        $this->paused = $paused;
        $this->visible = $visible;
    }
    
    public function getFormat(): string
    {
        return $this->format;
    }
    
    public function getTime(): int
    {
        return $this->time;
    }
    
    public function isPaused(): bool
    {
        return $this->paused;
    }
    
    public function isVisible(): bool
    {
        return $this->visible;
    }
    
    public function setTime(int $time): void
    {
        $this->time = $time;
    }
    
    public function setPaused(bool $value): void
    {
        $this->paused = $value;
    }
    
    public function setVisible(bool $value): void
    {
        $this->visible = $value;
    }
    
    public function update(): void
    {
        if (!$this->isPaused())
            $this->time--;
    }
}