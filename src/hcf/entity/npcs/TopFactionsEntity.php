<?php

declare(strict_types=1);

namespace hcf\entity\npcs;

use Himbeer\LibSkin\SkinConverter;
use itoozh\Leaderboards\Leaderboards;
use JetBrains\PhpStorm\Pure;
use hcf\faction\Faction;
use hcf\HCFLoader;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\VanillaItems;

class TopFactionsEntity extends Human
{
    
    public bool $canCollide = false;
    protected bool $immobile = true;
	protected function getInitialDragMultiplier() : float{ return 0.00; }

	protected function getInitialGravity() : float{ return 0.00; }

    /**
     * @param Player $player
     * @return TopFactionsEntity
     */
    public static function create(Player $player): self
    {
        $nbt = CompoundTag::create()
            ->setTag('Pos', new ListTag([
                new DoubleTag($player->getLocation()->x),
                new DoubleTag($player->getLocation()->y),
                new DoubleTag($player->getLocation()->z)
            ]))
            ->setTag('Motion', new ListTag([
                new DoubleTag($player->getMotion()->x),
                new DoubleTag($player->getMotion()->y),
                new DoubleTag($player->getMotion()->z)
            ]))
            ->setTag('Rotation', new ListTag([
                new FloatTag($player->getLocation()->yaw),
                new FloatTag($player->getLocation()->pitch)
            ]));
        return new self($player->getLocation(), $player->getSkin(), $nbt);
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    private function getFactions(): array
    {
        $points = [];

        foreach (HCFLoader::getInstance()->getFactionManager()->getFactions() as $name => $faction) {
            if (in_array($faction->getName(), ['Spawn', 'North Road', 'South Road', 'East Road', 'West Road', 'Nether Spawn', 'End Spawn', 'Nether North Road', 'Nether South Road', 'Nether West Road', 'Nether East Road', '§5Citadel§c', 'Glowstonemontain']))
                continue;
            $points[$name] = $faction->getPoints();
        }
        return $points;
    }

    /**
     * @param int $currentTick
     *
     * @return bool
     * @throws \JsonException
     * @throws \Exception
     */
    public function onUpdate(int $currentTick): bool
    {

        //if(count($this->getFactions()) > 0){}
        $data = $this->getFactions();
        arsort($data);

        $text = TextFormat::colorize("§l§gTops Factions§r\n");

        $top = 1;

        foreach($data as $name => $points) {
            $line = TextFormat::colorize("&e#{$top}. &f{$name} - &7[&c{$points}&7]\n");
            $text .= $line;

            if($top >= 10) {
                break;
            }
            $top++;
        }

        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag($text);
        $this->setScale(0.01);
        
        $nearest = $this->location->world->getNearestEntity($this->location, 8, Player::class);
        
        if ($nearest === null)
            return parent::onUpdate($currentTick);
        $this->lookAt($nearest->getEyePos());
        return parent::onUpdate($currentTick);
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void {
        $source->cancel();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if ($damager instanceof Player) {
                if ($damager->hasPermission('npc.command') && $damager->getInventory()->getItemInHand()->getCustomName() === "§eRemove NPC §r§7(Right Click)") {
                    $this->kill();
                    return;
                }
            }
        }

    }
}