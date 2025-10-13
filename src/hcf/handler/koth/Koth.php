<?php

declare(strict_types=1);

namespace hcf\handler\koth;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\Utils;
use itoozh\crates\Main;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Koth {

    private ?Player $capturer = null;

    private string $name;
    private int $time, $progress, $points, $captureAnnounceTick = 0, $capturerCheckTick = 0, $progressMessageTick = 0;
    private ?string $coords;
    private ?KothCapzone $capzone = null;

    public function __construct(string $name, int $time, int $points, ?string $coords, ?array $claim, ?array $capzone) {
        $this->name = $name;
        $this->time = $time;
        $this->progress = $time;
        $this->points = $points;
        $this->coords = $coords;

        if ($claim !== null) {
            HCFLoader::getInstance()->getClaimManager()->createClaim(
                $name, 'koth',
                (int)$claim['minX'], (int)$claim['maxX'],
                (int)$claim['minZ'], (int)$claim['maxZ'],
                $claim['world']
            );
        }

        if ($capzone !== null) {
            $this->capzone = new KothCapzone(
                (int)$capzone['minX'], (int)$capzone['maxX'],
                (int)$capzone['minY'], (int)$capzone['maxY'],
                (int)$capzone['minZ'], (int)$capzone['maxZ'],
                $capzone['world']
            );
        }
    }

    public function getName(): string { return $this->name; }
    public function getTime(): int { return $this->time; }
    public function getProgress(): int { return $this->progress; }
    public function getPoints(): int { return $this->points; }
    public function getCoords(): ?string { return $this->coords; }
    public function getCapzone(): ?KothCapzone { return $this->capzone; }

    public function setTime(int $time): void { $this->time = $time; }
    public function setProgress(int $time): void { $this->progress = $time; }
    public function setPoints(int $points): void { $this->points = $points; }
    public function setCoords(?string $coords): void { $this->coords = $coords; }
    public function setCapzone(KothCapzone $capzone): void { $this->capzone = $capzone; }

    public function update(): void {
        if ($this->capturer === null) {
            if (++$this->capturerCheckTick >= 0) {
                $this->capturerCheckTick = 0;
                $world = Server::getInstance()->getWorldManager()->getWorldByName($this->capzone?->getWorld() ?? '');
                if ($world === null) return;

                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if (!$player instanceof Player) continue;
                    $session = $player->getSession();
                    if (
                        $this->capzone?->inside($player->getPosition()) &&
                        $session->getFaction() !== null &&
                        $session->getCooldown('pvp.timer') === null &&
                        $session->getCooldown('starting.timer') === null
                    ) {
                        $this->capturer = $player;
                        $factionName = $session->getFaction();
                        $prefix = $this->name === "Citadel" ? "&r&6[§uCitadel§6]" : "&4[KingOfTheHill]";
                        $message = $this->name === "Citadel"
                            ? "&eThe faction &a{$factionName} &eis now controlling &3{$this->name}&e!"
                            : "&7The faction &a{$factionName} &7has started capturing &f{$this->name}&7!";
                        Server::getInstance()->broadcastMessage(TextFormat::colorize("$prefix $message"));
                        $this->captureAnnounceTick = 0;
                        break;
                    }
                }
            }
        } else {
            if (!$this->capturer->isOnline() || !$this->capzone?->inside($this->capturer->getPosition())) {
                $this->resetCapture();
                return;
            }

            if (++$this->captureAnnounceTick >= 60) {
                Server::getInstance()->broadcastMessage(TextFormat::colorize("§e[§lKOTH§r§e] §f{$this->capturer->getName()} §eis still controlling §a{$this->name}§e!"));
                $this->captureAnnounceTick = 0;
            }

            if (++$this->progressMessageTick >= 60 && $this->progress > 0) {
                $this->progressMessageTick = 0;
                $faction = $this->capturer->getSession()->getFaction();
                $formattedTime = gmdate("i:s", $this->progress);
                $prefix = $this->name === "Citadel" ? "&r&6[§uCitadel§6]" : "&4[KingOfTheHill]";
                $message = $this->name === "Citadel"
                    ? "&eLa faction &a{$faction} &eestá capturando &3{$this->name}&e! Tiempo restante: &b{$formattedTime}"
                    : "&7La faction &a{$faction} &7está capturando &f{$this->name}&7! Tiempo restante: &e{$formattedTime}";
                Server::getInstance()->broadcastMessage(TextFormat::colorize("$prefix $message"));
            }

            if ($this->progress-- <= 0) {
                $session = $this->capturer->getSession();
                $factionName = $session->getFaction();
                $faction = HCFLoader::getInstance()->getFactionManager()->getFaction($factionName);

                $faction->setPoints($faction->getPoints() + $this->points);
                $faction->setKothCaptures($faction->getKothCaptures() + 1);

                $keys = Main::getCrate("Koth")->getKeyItem(5);
                if ($this->capturer->getInventory()->canAddItem($keys)) {
                    $this->capturer->getInventory()->addItem($keys);
                } else {
                    $this->capturer->getWorld()->dropItem($this->capturer->getPosition(), $keys);
                }

                if ($this->name !== "Citadel") {
                    $webHook = new Webhook(HCFLoader::getInstance()->getConfig()->get('koth.webhook'));
                    $msg = new Message();
                    $embed = new Embed();
                    $embed->setColor(0xFF0000);
                    $embed->addField("KotH capturado", "{$this->name} fue capturado por {$this->capturer->getName()}");
                    $embed->setFooter("legendsmc.fun:19132");
                    $msg->addEmbed($embed);
                    $webHook->send($msg);
                    Utils::kothcontroller();
                } else {
                    $messages = [
                        "&7███████",
                        "&7█&3█&7███&3█&7█",
                        "&7█&3█&7██&3█&7██ &r&6[KingOfTheHill]",
                        "&7█&3███&7███ &r&9{$this->name} &efue capturado por &6[&e{$factionName}&6] {$this->capturer->getName()}&e!",
                        "&7█&3█&7██&3█&7██ &r&6[KingOfTheHill] &6[&e{$factionName}&6] {$this->capturer->getName()}&e.",
                        "&7█&3█&7███&3█&7█",
                        "&7█&3█&7███&3█&7█",
                        "&7███████"
                    ];
                    foreach ($messages as $line) {
                        Server::getInstance()->broadcastMessage(TextFormat::colorize($line));
                    }
                    Utils::kothcontroller();
                }

                $this->resetCapture();
                HCFLoader::getInstance()->getKothManager()->setKothActive(null);
            }
        }
    }

    private function resetCapture(): void {
        $this->progress = $this->time;
        $this->capturer = null;
        $this->captureAnnounceTick = 0;
    }

    public function getData(): array {
        $data = [
            'time' => $this->time,
            'points' => $this->points,
            'coords' => $this->coords,
            'claim' => null,
            'capzone' => null
        ];

        $claim = HCFLoader::getInstance()->getClaimManager()->getClaim($this->name);
        if ($claim !== null) {
            $data['claim'] = [
                'minX' => $claim->getMinX(),
                'maxX' => $claim->getMaxX(),
                'minZ' => $claim->getMinZ(),
                'maxZ' => $claim->getMaxZ(),
                'world' => $claim->getWorld()
            ];
        }

        if ($this->capzone !== null) {
            $data['capzone'] = [
                'minX' => $this->capzone->getMinX(),
                'maxX' => $this->capzone->getMaxX(),
                'minY' => $this->capzone->getMinY(),
                'maxY' => $this->capzone->getMaxY(),
                'minZ' => $this->capzone->getMinZ(),
                'maxZ' => $this->capzone->getMaxZ(),
                'world' => $this->capzone->getWorld()
            ];
        }

        return $data;
    }
}