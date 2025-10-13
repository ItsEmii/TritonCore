<?php

declare(strict_types=1);

namespace hcf\handler\koth;

use hcf\HCFLoader;
use hcf\handler\koth\command\KothCommand;

class KothManager
{
    private array $koths = [];
    private ?string $kothActive = null;

    public function __construct()
    {
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new KothCommand());

        foreach (HCFLoader::getInstance()->getProvider()->getKoths() as $name => $data) {
            $this->createKoth(
                name: $name,
                time: (int) $data['time'],
                points: (int) $data['points'],
                coords: $data['coords'] ?? null,
                claim: $data['claim'] ?? null,
                capzone: $data['capzone'] ?? null
            );
        }
    }

    public function getKoths(): array
    {
        return $this->koths;
    }

    public function getKoth(string $name): ?Koth
    {
        return $this->koths[$name] ?? null;
    }

    public function getKothActive(): ?string
    {
        return $this->kothActive;
    }

    public function createKoth(
        string $name,
        int $time,
        int $points = 6,
        ?string $coords = null,
        ?array $claim = null,
        ?array $capzone = null
    ): void {
        $this->koths[$name] = new Koth($name, $time, $points, $coords, $claim, $capzone);
    }

    public function removeKoth(string $name): void
    {
        unset($this->koths[$name]);
    }

    public function setKothActive(?string $name): void
    {
        $this->kothActive = $name;
    }
}