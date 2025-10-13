<?php

declare(strict_types=1);

namespace hcf\timer;

use hcf\timer\command\EotwCommand;
use hcf\timer\command\KeyallCommand;
use hcf\timer\command\KeyallopCommand;
use hcf\timer\command\PkgallCommand;
use hcf\timer\command\PurgeCommand;
use hcf\timer\command\SotwCommand;
use hcf\timer\command\x2PointsCommand;
use hcf\timer\types\TimerEotw;
use hcf\timer\types\TimerKeyAll;
use hcf\timer\types\TimerKeyAllOP;
use hcf\timer\types\TimerPackages;
use hcf\timer\types\TimerPurge;
use hcf\timer\types\TimerSotw;
use hcf\timer\types\Timerx2Points;
use hcf\HCFLoader;
use hcf\timer\command\CustomTimerCommand;
use hcf\timer\types\TimerCustom;

/**
 * Class TimerManager
 * @package hcf\timer
 */
class TimerManager
{
    
    private TimerSotw $sotw;
    
    private TimerEotw $eotw;

    private TimerPurge $purge;
    
    private Timerx2Points $points;
    
    private TimerKeyAll $keyAll;
    
    private TimerKeyAllOP $keyAllOP;
    
    private TimerPackages $packages;

    private array $customTimers = [];
    
    /**
     * TimerManager construct.
     */
    public function __construct()
    {
        # Setup main events
        $this->sotw = new TimerSotw;
        $this->eotw = new TimerEotw;
        $this->purge = new TimerPurge;
        $this->points = new Timerx2Points;
        $this->keyAll = new TimerKeyAll;
        $this->keyAllOP = new TimerKeyAllOP;
        $this->packages = new TimerPackages;
        
        # Register commands
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new EotwCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new x2PointsCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new SotwCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new PurgeCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new CustomTimerCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new EventCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new KeyallCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new KeyallopCommand());
        HCFLoader::getInstance()->getServer()->getCommandMap()->register('HCF', new PkgallCommand());
        
        # Register listener
        HCFLoader::getInstance()->getServer()->getPluginManager()->registerEvents(new TimerListener(), HCFLoader::getInstance());
    }
    
    /**
     * @return TimerSotw
     */
    public function getSotw(): TimerSotw
    {
        return $this->sotw;
    }
    
    public function getPoints(): Timerx2Points
    {
        return $this->points;
    }
    
    /**
     * @return TimerEotw
     */
    public function getEotw(): TimerEotw
    {
        return $this->eotw;
    }

    public function getPurge(): TimerPurge
    {
        return $this->purge;
    }

    public function getKeyAll(): TimerKeyAll
    {
        return $this->keyAll;
    }

    public function getKeyAllOP(): TimerKeyAllOP
    {
        return $this->keyAllOP;
    }


    public function getPackages(): TimerPackages
    {
        return $this->packages;
    }

    public function getCustomTimers(): array {
        return $this->customTimers;
    }

    public function getCustomTimerByName(string $name): TimerCustom {
        return $this->customTimers[$name];
    }

    public function addCustomTimer(string $name): void {
        $this->customTimers[$name] = new TimerCustom($name);
    }

    public function removeCustomTimer(string $name): void {
        unset($this->customTimers[$name]);
    }

    public function hasCustomTimer(string $name): bool {
        return isset($this->customTimers[$name]);
    }

}