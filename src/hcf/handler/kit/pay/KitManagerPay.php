<?php

declare(strict_types=1);

namespace hcf\handler\kit\pay;

use hcf\HCFLoader;
use hcf\handler\kit\classes\ClassFactory;
use hcf\handler\kit\command\KitCommand;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class KitManagerPay
{

    private array $kits = [];
    
    public function __construct(){
        # Register kits
        foreach (HCFLoader::getInstance()->getProvider()->getKitsPay() as $name => $data) {
            if ($data['permission'] !== null) {
                $this->registerPermission($data['permission']);
            }
            $this->addKit($name, $data['nameFormat'], $data['permission'], $data['representativeItem'], $data['items'] ?? [], $data['armor'] ?? [], $data['cooldown'] ?? 0, false);
        }
        HCFLoader::getInstance()->getServer()->getPluginManager()->registerEvents(new KitListenerPay(), HCFLoader::getInstance());
    }
    
    public function registerPermission(string $permission): void {
        $manager = PermissionManager::getInstance();
        $manager->addPermission(new Permission($permission));
        $manager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild($permission, true);
    }
    
    public function callEvent(string $method, Event $event): void {
        foreach (ClassFactory::getClasses() as $class) {
            $class->$method($event);
        }
    }

    /**
     * @return Kit[]
     */
    public function getKits(): array
    {
        return $this->kits;
    }
    
    /**
     * @return string[]
     */
    public function getOrganization(): array
    {
        return HCFLoader::getInstance()->getProvider()->getKitPayConfig()->get('organization');
    }
    
    /**
     * @param string $kitName
     * @return Kit|null
     */
    public function getKit(string $kitName): ?KitPay
    {
        return $this->kits[$kitName] ?? null;
    }
    
    /**
     * @param string $kitName
     * @param string $nameFormat
     * @param string|null $permission
     * @param Item|null $itemRepresentative
     * @param Item[] $items
     * @param Item[] $armor
     * @param int $cooldown
     * @param bool $new
     */
    public function addKit(string $kitName, string $nameFormat, ?string $permission, ?Item $itemRepresentative, array $items, array $armor, int $cooldown, bool $new = true): void
    {
        $this->kits[$kitName] = new KitPay($kitName, $nameFormat, $permission, $itemRepresentative, $items, $armor, $cooldown);
        
        if ($new) {
            # Organization
            $organization = $this->getOrganization();
            if(isset($organization[$kitName])) return;
            $organization[] = $kitName;
            HCFLoader::getInstance()->getProvider()->getKitPayConfig()->set('organization', $organization);
            HCFLoader::getInstance()->getProvider()->getKitPayConfig()->save();
        }
    }

    /**
     * @param string $kitName
     * @throws \JsonException
     */
    public function removeKit(string $kitName): void
    {
        unset($this->kits[$kitName]);
        
        # Organization
        $organization = $this->getOrganization();
        $key = array_search($kitName, $organization);
        unset($organization[$key]);
        HCFLoader::getInstance()->getProvider()->getKitPayConfig()->set('organization', $organization);
        HCFLoader::getInstance()->getProvider()->getKitPayConfig()->save();
    }

    /**
     * @param string[] $organization
     * @throws \JsonException
     */
    public function setOrganization(array $organization): void
    {
        HCFLoader::getInstance()->getProvider()->getKitPayConfig()->set('organization', $organization);
        HCFLoader::getInstance()->getProvider()->getKitPayConfig()->save();
    }

}