<?php

declare(strict_types=1);

namespace hcf\timer\types;

use hcf\utils\Utils;
use pocketmine\Server;

class TimerPurge
{
    public function __construct(
        private int $time = 60 * 60,
        private string $format = '',
        private bool $active = false
    ) {}

    public function getTime(): int
    {
        return $this->time;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setTime(int $value): void
    {
        $this->time = $value;
    }

    public function setActive(bool $value): void
    {
        $this->active = $value;
    }

    public function update(): void
    {
        if ($this->active) {
            if ($this->time === 300) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendPopup("§4¡EL PURGE TERMINA EN 5 MINUTOS!");

                    Utils::PlaySound($player, "random.orb", 1, 1);
                }
            }

            $this->time--;

            if ($this->time <= 0) {
                $this->active = false;
                $this->time = 60 * 60;
            }
        }
    }
}