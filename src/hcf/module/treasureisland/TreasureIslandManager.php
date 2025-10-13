<?php

namespace hcf\module\treasureisland;

use hcf\HCFLoader;
use hcf\utils\serialize\Serialize;
use hcf\module\treasureisland\command\TreasureCommand;
use pocketmine\block\Chest as BlockChest;
use pocketmine\block\tile\Chest;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class TreasureIslandManager {

    private array $items = [];
    private Config $config;
    private int $lastRefill = 0;
    private int $intervalTicks = 8 * 60 * 60 * 20;

    public function __construct() {
        $plugin = HCFLoader::getInstance();

        $path = $plugin->getDataFolder() . "others/treasure.yml";
        $this->config = new Config($path, Config::YAML);
        foreach ($this->config->get("treas", []) as $data) {
            $item = Serialize::deserialize($data);
            if ($item instanceof Item) {
                $this->items[] = $item;
            }
        }

        $plugin->getServer()->getCommandMap()->register("HCF", new TreasureCommand());
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->tick();
        }), 20 * 60 * 5);
    }

    public function setItems(array $items): void {
        $this->items = $items;

        $serialized = [];
        foreach ($items as $index => $item) {
            $serialized[$index] = Serialize::serialize($item);
        }

        $this->config->set("treas", $serialized);
        $this->config->save();
    }

    public function getRandomItems(): array {
        if (empty($this->items)) return [];

        $available = $this->items;
        shuffle($available);
        return array_slice($available, 0, min(rand(6, 12), count($available)));
    }

    public function update(): void {
        $claim = HCFLoader::getInstance()->getClaimManager()->getClaim("TreasureIsland");
        if ($claim === null) return;

        $world = HCFLoader::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
        $pos1 = new Vector3($claim->getMinX(), 0, $claim->getMinZ());
        $pos2 = new Vector3($claim->getMaxX(), 255, $claim->getMaxZ());

        for ($x = $pos1->getX(); $x <= $pos2->getX(); $x++) {
            for ($y = $pos1->getY(); $y <= $pos2->getY(); $y++) {
                for ($z = $pos1->getZ(); $z <= $pos2->getZ(); $z++) {
                    $block = $world->getBlockAt($x, $y, $z);
                    if ($block instanceof BlockChest) {
                        $tile = $world->getTileAt($x, $y, $z);
                        if ($tile instanceof Chest) {
                            $tile->getInventory()->setContents($this->getRandomItems());
                        }
                    }
                }
            }
        }

        $this->lastRefill = time();
    }

    public function forceUpdateWithEffects(string $executorName): void {
        $this->update();

        $server = HCFLoader::getInstance()->getServer();
        $server->broadcastMessage("§e[§6TreasureIsland§e] the chests have been filled by §a{$executorName}!");

        foreach ($server->getOnlinePlayers() as $player) {
            $player->sendTitle("§6§lTreasureIsland", "§fChests have been refilled by §a{$executorName}!", 10, 60, 10);
            $player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create(
                "random.levelup",
                $player->getLocation()->getX(),
                $player->getLocation()->getY(),
                $player->getLocation()->getZ(),
                1,
                1
            ));
        }
    }

    public function tick(): void {
        $timeSince = time() - $this->lastRefill;
        $intervalSeconds = (int)($this->intervalTicks / 20);
        $timeLeft = max($intervalSeconds - $timeSince, 0);

        if ($timeLeft === 0) {
            $this->forceUpdateWithEffects("System");
            return;
        }

        $formatted = $this->formatTimeLeft($timeLeft);
        HCFLoader::getInstance()->getServer()->broadcastMessage("§e[§6TreasureIsland§e] §fChests will be refilled in §c{$formatted}");
    }

    private function formatTimeLeft(int $seconds): string {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        $parts = [];
        if ($h > 0) $parts[] = "{$h}h";
        if ($m > 0) $parts[] = "{$m}m";
        if ($h === 0 && $m === 0) $parts[] = "{$s}s";

        return implode(" ", $parts);
    }
}