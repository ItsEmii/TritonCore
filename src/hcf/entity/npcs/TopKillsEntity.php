<?php

declare(strict_types=1);

namespace hcf\entity\npcs;

use Himbeer\LibSkin\SkinConverter;
use itoozh\Leaderboards\Leaderboards;
use JetBrains\PhpStorm\Pure;
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

class TopKillsEntity extends Human
{
    
    public bool $canCollide = false;
    protected bool $immobile = true;

	protected function getInitialDragMultiplier() : float{ return 0.00; }

	protected function getInitialGravity() : float{ return 0.00; }

    /** @var int|null */

    /**
     * @param Player $player
     *
     * @return TopKillsEntity
     */
    public static function create(Player $player): self
    {
        $nbt = CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($player->getLocation()->x),
                new DoubleTag($player->getLocation()->y),
                new DoubleTag($player->getLocation()->z)
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($player->getMotion()->x),
                new DoubleTag($player->getMotion()->y),
                new DoubleTag($player->getMotion()->z)
            ]))
            ->setTag("Rotation", new ListTag([
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
    private function getKills(): array
    {
        $kills = [];

        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            $kills[$session->getName()] = $session->getKills();
        }
        return $kills;
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
        $data = $this->getKills();
        arsort($data);

        $text = TextFormat::colorize("§l§gTops Kills§r\n");

        $top = 1;
        foreach($data as $name => $kills){
            $line = TextFormat::colorize("&e#{$top}. &f{$name} - &7[&c{$kills}&7]\n");
            $text .= $line;
            if($top >= 10) {
                break;
            }
            $top++;
        }

        $this->setNameTag($text);
        $this->setNameTagAlwaysVisible(true);
        $this->setScale(0.0001);
                
        //$skinData = SkinConverter::imageToSkinDataFromPngPath(HCFLoader::getInstance()->getDataFolder() . 'Skins/' . $player[0] . '.png');
        //$this->setSkin(new Skin('top_kdr_skin', $skinData));
        
        //if ($currentTick % 2400 === 0) {}
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