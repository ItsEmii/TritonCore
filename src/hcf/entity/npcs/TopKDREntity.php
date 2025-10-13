<?php

declare(strict_types=1);

namespace hcf\entity\npcs;

use Himbeer\LibSkin\SkinConverter;
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
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class TopKDREntity extends Human
{
    
    public bool $canCollide = false;
    protected bool $immobile = true;

	protected function getInitialDragMultiplier() : float{ return 0.00; }

	protected function getInitialGravity() : float{ return 0.00; }

    /** @var int|null */

    /**
     * @param Player $player
     *
     * @return TopKDREntity
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
    private function getKDR(): array
    {
        $kdr = [];

        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            if ($session->getDeaths() === 0)
                $kdr[$session->getName()] = 0.0;
            else
                $kdr[$session->getName()] = round($session->getKills() / $session->getDeaths(), 1);
        }
        return $kdr;
    }

    /**
     * @param int $currentTick
     *
     * @return bool
     * @throws \Exception
     */
    public function onUpdate(int $currentTick): bool
    {
        $kdrData = $this->getKDR();
        arsort($kdrData); 

        $text = TextFormat::colorize("§l§gTops KDR§r\n");

        $top = 1;

        foreach($kdrData as $name => $kdr){
            $line = TextFormat::colorize("&e#{$top}. &f{$name} - &7[&c{$kdr}&7]\n");
            $text .= $line;

            if($top >= 10){
                break; 
            }
            $top++;
        }

        $this->setNameTagAlwaysVisible();
        $this->setNameTag($text);
        $this->setScale(0.01);
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