<?php

declare(strict_types=1);

namespace hcf\timer\types;

use hcf\HCFLoader;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use hcf\utils\Utils;

class TimerKeyAllOP
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
            if ($this->time === 10) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendPopup("§l§e¡KeyAllOP se entregará en 10 segundos!");

                    Utils::PlaySound($player, "random.orb", 1, 1);
                }
            }

            if ($this->time === 1) {
                foreach (HCFLoader::getInstance()->getConfig()->get("KeyallOP") as $key => $amount) {
                    $keyName = (string)$key;
                    $keyAmount = (int)$amount;
                    Server::getInstance()->dispatchCommand(
                        new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()),
                        "key giveall $keyName $keyAmount"
                    );
                }

                Server::getInstance()->broadcastMessage("§aKeyAllOP entregado correctamente.");
            }

            $this->time--;

            if ($this->time <= 0) {
                $this->active = false;
                $this->time = 60 * 60;
            }
        }
    }
}