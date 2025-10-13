<?php

declare(strict_types=1);

namespace hcf\player\session;

use hcf\HCFLoader;

class SessionManager
{
    
    private array $sessions = [];
    
    public function __construct()
    {
        foreach (HCFLoader::getInstance()->getProvider()->getPlayers() as $xuid => $data)
            $this->addSession((string) $xuid, $data, false);
    }
    
    /**
     * @return array
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }
    
    public function getSession(string $xuid): ?Session
    {
        return $this->sessions[$xuid] ?? null;
    }
    
    public function addSession(string $xuid, array $data, bool $firstTime = true): void
    {
        $this->sessions[$xuid] = new Session($xuid, $data, $firstTime);
    }
}