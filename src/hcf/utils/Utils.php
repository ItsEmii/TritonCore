<?php

namespace hcf\utils;

use hcf\claim\Claim;
use hcf\HCFLoader;
use http\Exception\RuntimeException;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Server;

class Utils
{
    public static function getRandomColor(): string {
        $colors = array_values(TextFormat::COLORS);
        return $colors[rand(0, count($colors) - 1)];
    }


    public static function getRandomNumber(float $min = 0.0, float $max = 1.0): float {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function equalsPosition(Position $pos1, Position $pos2): bool {
        return strtolower($pos1->getFloorX()) == strtolower($pos2->getFloorX()) && strtolower($pos1->getFloorY()) == strtolower($pos2->getFloorY()) && strtolower($pos1->getFloorZ()) == strtolower($pos2->getFloorZ()) && strtolower($pos1->getWorld()->getFolderName()) == strtolower($pos2->getWorld()->getFolderName());
    }

    private function __construct() { }

    public static function array_shift_circular(array $array, int $steps = 1): array {
        if($steps == 0) {
            return $array;
        }

        if(($l = count($array)) == 0) {
            return $array;
        }
        $steps = ($steps % $l) * -1;

        return array_merge(
            array_slice($array, $steps),
            array_slice($array, 0, $steps)
        );
    }

    /**
     * Returns a new array with the values of the old array padded
     * on the center of the new array given a specified size
     *
     * @param array $array
     * @param int $size
     * @param mixed $padding
     *
     * @return array
     *
     */
    public static function array_center_pad(array $array, int $size, mixed $padding): array {
        $base = array_fill(0, $size, $padding);
        $startIndex = max((int)(floor($size / 2) - floor(count($array) / 2)), 0);
        $vals = array_values($array);
        foreach($vals as $i => $value) {
            $base[$startIndex + $i] = $value;
        }

        return $base;
    }

    public static function has_string_keys(array $array): bool {
        // https://stackoverflow.com/a/4254008/7126351
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    public static function addSound(Vector3 $position, int $sound) : LevelSoundEventPacket {
        return LevelSoundEventPacket::nonActorSound($sound, $position, false, -1);
    }

    public static function addParticle(Vector3 $position, string $particleName) : SpawnParticleEffectPacket {
        return SpawnParticleEffectPacket::create(DimensionIds::OVERWORLD, -1, $position, $particleName, null);
    }

    public static function PlaySound(Player $player, string $sound, int $volume, float $pitch){
		$packet = new PlaySoundPacket();
		$packet->x = $player->getPosition()->getX();
		$packet->y = $player->getPosition()->getY();
		$packet->z = $player->getPosition()->getZ();
		$packet->soundName = $sound;
		$packet->volume = $volume;
		$packet->pitch = $pitch;
		$player->getNetworkSession()->sendDataPacket($packet);
	}

    public static function BroadSound(Player $player, string $soundName, int $volume, float $pitch){
        $packet = new PlaySoundPacket();
        $packet->soundName = $soundName;
        $position = $player->getPosition();
        $packet->x = $position->getX();
        $packet->y = $position->getY();
        $packet->z = $position->getZ();
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $world = $position->getWorld();
        NetworkBroadcastUtils::broadcastPackets($world->getPlayers(), [$packet]);
    }

    public static function kothstart() : void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            Utils::PlaySound($player, "mob.wither.spawn", 1, 1);
        }
    }

    public static function kothcontroller() : void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            Utils::PlaySound($player, "ambient.weather.lightning.impact", 1, 1);
            Utils::PlaySound($player, "firework.twinkle", 1, 1);
        }
    }

    public static function vector3ToString(Vector3 $vector3): string {
        return $vector3->getFloorX() . ':' . $vector3->getFloorY() . ':' . $vector3->getFloorZ();
    }

    /**
     * @param string $string
     *
     * @return Vector3
     */
    public static function stringToVector3(string $string): Vector3 {
        $args = explode(':', $string);
        return new Vector3((float) $args[0],(float) $args[1],(float) $args[2]);
    }
    
    public static function playLight(Position $pos): void {
        $pk = new AddActorPacket();
        $pk->actorUniqueId = Entity::nextRuntimeId();
        $pk->actorRuntimeId = 1;
        $pk->position = $pos->asVector3();
        $pk->type = "minecraft:lightning_bolt";
        $pk->yaw = 0;
        $pk->syncedProperties = new PropertySyncData([], []);
        $sound = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 1, 1);
        NetworkBroadcastUtils::broadcastPackets($pos->getWorld()->getPlayers(), [$pk, $sound]);
    }

    public static function claimToString(Claim $l, $separator = '-') : string {
        return $l->getMinX().$separator.$l->getMaxX().$separator.$l->getMinZ().$separator.$l->getMaxZ().$separator.null;
    }

    public static function s1(string $data) : Position {
        $data = explode(':', $data);
        return new Position((float) $data[1], (float) $data[2], (float) $data[3], Server::getInstance()->getWorldManager()->getWorldByName($data[0]));
    }

    public static function p1(Position $position) : string {
        return $position->getWorld()->getFolderName().":" . round($position->getX()).":".round($position->getY()).":".round($position->getZ());
    }
    
}