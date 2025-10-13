<?php

declare(strict_types=1);

namespace hcf\provider;

use hcf\HCFLoader;
use hcf\utils\serialize\Serialize;
use pocketmine\utils\Config;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class Provider {
    public Config $kothConfig, $claimConfig, $kitConfig, $kitPayConfig, $kitLConfig;

    public function __construct() {
        $plugin = HCFLoader::getInstance();

        @mkdir($plugin->getDataFolder() . 'database/players', 0777, true);
        @mkdir($plugin->getDataFolder() . 'database/factions', 0777, true);
        @mkdir($plugin->getDataFolder() . 'kits', 0777, true);

        $plugin->saveDefaultConfig();

        $this->kothConfig = new Config($plugin->getDataFolder() . 'database/koths.yml', Config::YAML);
        $this->claimConfig = new Config($plugin->getDataFolder() . 'database/claims.yml', Config::YAML);
        $this->kitPayConfig = new Config($plugin->getDataFolder() . 'kits/kitsPay.yml', Config::YAML, ["organization" => [], "kits" => []]);
        $this->kitConfig = new Config($plugin->getDataFolder() . 'kits/kitsFree.yml', Config::YAML, ["organization" => [], "kits" => []]);
        $this->kitLConfig = new Config($plugin->getDataFolder() . 'kits/kitsL.yml', Config::YAML, ["organization" => [], "kits" => []]);

        $this->kothConfig->reload();
        $this->claimConfig->reload();
        $this->kitPayConfig->reload();
        $this->kitConfig->reload();
        $this->kitLConfig->reload();
    }

    public function save(): void {
        $plugin = HCFLoader::getInstance();
        $server = $plugin->getServer();

        $playersData = [];
        foreach ($plugin->getSessionManager()->getSessions() as $name => $session) {
            $playersData[$name] = $session->getData();
        }

        $factionsData = [];
        foreach ($plugin->getFactionManager()->getFactions() as $name => $faction) {
            $factionsData[$name] = $faction->getData();
        }

        $kothsData = [];
        foreach ($plugin->getKothManager()->getKoths() as $koth) {
            $kothsData[$koth->getName()] = $koth->getData();
        }

        $claimsData = [];
        foreach ($plugin->getClaimManager()->getClaims() as $name => $claim) {
            $claimsData[$name] = $claim->getData();
        }

        $kitsData = [];
        foreach ($plugin->getHandlerManager()->getKitManager()->getKits() as $kit) {
            $kitsData[$kit->getName()] = $kit->getData();
        }

        $kitsPayData = [];
        foreach ($plugin->getHandlerManager()->getKitPayManager()->getKits() as $kit) {
            $kitsPayData[$kit->getName()] = $kit->getData();
        }

        $kitsLData = [];
        foreach ($plugin->getHandlerManager()->getKitLManager()->getKits() as $kit) {
            $kitsLData[$kit->getName()] = $kit->getData();
        }
        

        $dataFolderPath = $plugin->getDataFolder();

        $server->getAsyncPool()->submitTask(new class(
            json_encode($playersData),
            json_encode($factionsData),
            json_encode($kothsData),
            json_encode($claimsData),
            json_encode($kitsData),
            json_encode($kitsPayData),
            json_encode($kitsLData),
            $dataFolderPath
        ) extends AsyncTask {
            public function __construct(
                private string $playersJson,
                private string $factionsJson,
                private string $kothsJson,
                private string $claimsJson,
                private string $kitsJson,
                private string $kitsPayJson,
                private string $kitsLJson,
                private string $dataFolderPath
            ) {}

            public function onRun(): void {
                $playersData = json_decode($this->playersJson, true);
                $factionsData = json_decode($this->factionsJson, true);
                $kothsData = json_decode($this->kothsJson, true);
                $claimsData = json_decode($this->claimsJson, true);
                $kitsData = json_decode($this->kitsJson, true);
                $kitsPayData = json_decode($this->kitsPayJson, true);
                $kitsLData = json_decode($this->kitsLJson, true);
                

                foreach ($playersData as $name => $data) {
                    $config = new Config($this->dataFolderPath . 'database/players/' . $name . '.yml', Config::YAML);
                    $config->setAll($data);
                    $config->save();
                }

                foreach ($factionsData as $name => $data) {
                    $config = new Config($this->dataFolderPath . 'database/factions/' . $name . '.yml', Config::YAML);
                    $config->setAll($data);
                    $config->save();
                }

                $kothConfig = new Config($this->dataFolderPath . 'database/koths.yml', Config::YAML);
                $kothConfig->setAll($kothsData);
                $kothConfig->save();

                $claimConfig = new Config($this->dataFolderPath . 'database/claims.yml', Config::YAML);
                $claimConfig->setAll($claimsData);
                $claimConfig->save();

                $kitConfig = new Config($this->dataFolderPath . 'kits/kitsFree.yml', Config::YAML);
                $kitConfig->set("kits", $kitsData);
                $kitConfig->save();

                $kitPayConfig = new Config($this->dataFolderPath . 'kits/kitsPay.yml', Config::YAML);
                $kitPayConfig->set("kits", $kitsPayData);
                $kitPayConfig->save();

                $kitLConfig = new Config($this->dataFolderPath . 'kits/kitsL.yml', Config::YAML);
                $kitLConfig->set("kits", $kitsLData);
                $kitLConfig->save();
                
            }

            public function onCompletion(): void {}
        });

        $server->getLogger()->debug("Async save task dispatched");
    }

    public function getKitLConfig(): Config { return $this->kitLConfig; }
    public function getKitPayConfig(): Config { return $this->kitPayConfig; }
    public function getKitConfig(): Config { return $this->kitConfig; }
    public function getKothConfig(): Config { return $this->kothConfig; }
    public function getClaimsConfig(): Config { return $this->claimConfig; }

    public function getClaims(): array {
        $claims = [];
        foreach ($this->claimConfig->getAll() as $name => $data) {
            $claims[$name] = $data;
        }
        return $claims;
    }

    public function getFactions(): array {
        $factions = [];
        $dataFolder = HCFLoader::getInstance()->getDataFolder();
        foreach (glob($dataFolder . 'database/factions/*.yml') as $file) {
            $factions[basename($file, '.yml')] = (new Config($file, Config::YAML))->getAll();
        }
        return $factions;
    }

    public function getPlayers(): array {
        $players = [];
        $dataFolder = HCFLoader::getInstance()->getDataFolder();
        foreach (glob($dataFolder . 'database/players/*.yml') as $file) {
            $players[basename($file, '.yml')] = (new Config($file, Config::YAML))->getAll();
        }
        return $players;
    }

    public function getKoths(): array {
        $koths = [];
        foreach ($this->kothConfig->getAll() as $name => $data) {
            $koths[$name] = $data;
        }
        return $koths;
    }

    public function getKits(): array {
        $kits = [];
        foreach ($this->kitConfig->get("kits") as $name => $data) {
            if (isset($data['representativeItem']) && $data['representativeItem'] !== null)
                $data['representativeItem'] = Serialize::deserialize($data['representativeItem']);

            if (isset($data['items'])) {
                foreach ($data['items'] as $slot => $item){
                    $data['items'][$slot] = Serialize::deserialize($item);
                }
            }

            if (isset($data['armor'])) {
                foreach ($data['armor'] as $slot => $armor){
                    $data['armor'][$slot] = Serialize::deserialize($armor);
                }
            }
            $kits[$name] = $data;
        }
        return $kits;
    }

    public function getKitsPay(): array {
        $kits = [];
        foreach ($this->kitPayConfig->get("kits") as $name => $data) {
            if (isset($data['representativeItem']) && $data['representativeItem'] !== null)
                $data['representativeItem'] = Serialize::deserialize($data['representativeItem']);

            if (isset($data['items'])) {
                foreach ($data['items'] as $slot => $item){
                    $data['items'][$slot] = Serialize::deserialize($item);
                }
            }

            if (isset($data['armor'])) {
                foreach ($data['armor'] as $slot => $armor){
                    $data['armor'][$slot] = Serialize::deserialize($armor);
                }
            }
            $kits[$name] = $data;
        }
        return $kits;
    }

    public function getKitsL(): array {
        $kits = [];
        foreach ($this->kitLConfig->get("kits") as $name => $data) {
            if (isset($data['representativeItem']) && $data['representativeItem'] !== null)
                $data['representativeItem'] = Serialize::deserialize($data['representativeItem']);

            if (isset($data['items'])) {
                foreach ($data['items'] as $slot => $item){
                    $data['items'][$slot] = Serialize::deserialize($item);
                }
            }

            if (isset($data['armor'])) {
                foreach ($data['armor'] as $slot => $armor){
                    $data['armor'][$slot] = Serialize::deserialize($armor);
                }
            }
            $kits[$name] = $data;
        }
        return $kits;
    }
}