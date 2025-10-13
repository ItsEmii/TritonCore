<?php

declare(strict_types=1);

namespace hcf\timer\types;

use pocketmine\player\Player;
use pocketmine\Server;
use hcf\utils\Utils;

class TimerSotw
{
    private int $time = 3600;
    private string $format = '';
    private bool $active = false;
    private array $disabled = [];

    public function __construct(int $time = 3600, string $format = '', bool $active = false)
    {
        $this->time = $time;
        $this->format = $format;
        $this->active = $active;
    }

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

    public function isDisabled(Player $player): bool
    {
        return isset($this->disabled[$player->getUniqueId()->toString()]);
    }

    public function setDisabled(Player $player): void
    {
        $this->disabled[$player->getUniqueId()->toString()] = true;
    }

    public function clearDisabled(): void
    {
        $this->disabled = [];
    }

    public function update(): void
    {
        if ($this->active) {
            if ($this->time === 300) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendPopup("§a¡El SOTW termina en 5 minutos!");
                     Utils::PlaySound($player, "random.orb", 1, 1);
                }
            }

            $this->time--;

            if ($this->time <= 0) {
                $this->active = false;
                $this->time = 3600;

                $this->clearDisabled();
            }
        }
    }
}