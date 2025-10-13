<?php

namespace hcf\abilities;

use hcf\abilities\entity\BallOfRangeEntity;
use hcf\abilities\entity\PortableBardEntity;
use hcf\abilities\entity\PortableMagueEntity;
use hcf\abilities\entity\SullCratEntity;
use hcf\abilities\entity\SwitcherEntity;
use hcf\abilities\items\AbilityDisabler;
use hcf\abilities\items\BallOfRange;
use hcf\abilities\items\EffectDisabler;
use hcf\abilities\items\ExoticBone;
use hcf\abilities\items\FlowerTank;
use hcf\abilities\items\GuardianAngel;
use hcf\abilities\items\MedKit;
use hcf\abilities\items\NinjaShear;
use hcf\abilities\items\PortableBard as ItemPortableBard;
use hcf\abilities\items\PortableMague as ItemPortableMague;
use hcf\abilities\items\PotionRefill;
use hcf\abilities\items\RichBill;
use hcf\abilities\items\Resistance;
use hcf\abilities\items\StromBreaker;
use hcf\abilities\items\SullCrat as ItemSullCrat;
use hcf\abilities\items\Switcher as ItemSwitcher;
use hcf\abilities\items\Strength;
use hcf\abilities\items\TimeStone;
use hcf\HCFLoader;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;

class AbilitiesManager implements Listener
{
    public function __construct()
    {
        $server = HCFLoader::getInstance()->getServer();
        $pluginManager = $server->getPluginManager();
        $commandMap = $server->getCommandMap();
        $commandMap->register('HCF', new AbilitiesCommand());

        $plugin = HCFLoader::getInstance();
        $pluginManager->registerEvents($this, $plugin);
        $pluginManager->registerEvents(new PotionRefill(), $plugin);
        $pluginManager->registerEvents(new ItemSwitcher(), $plugin);
        $pluginManager->registerEvents(new TimeStone(), $plugin);
        $pluginManager->registerEvents(new ExoticBone(), $plugin);
        $pluginManager->registerEvents(new EffectDisabler(), $plugin);
        $pluginManager->registerEvents(new ItemPortableBard(), $plugin);
        $pluginManager->registerEvents(new AbilityDisabler(), $plugin);
        $pluginManager->registerEvents(new BallOfRange(), $plugin);
        $pluginManager->registerEvents(new StromBreaker(), $plugin);
        $pluginManager->registerEvents(new Resistance(), $plugin);
        $pluginManager->registerEvents(new Strength(), $plugin);
        $pluginManager->registerEvents(new NinjaShear(), $plugin);
        $pluginManager->registerEvents(new RichBill(), $plugin);
        $pluginManager->registerEvents(new FlowerTank(), $plugin);
        $pluginManager->registerEvents(new GuardianAngel(), $plugin);
        $pluginManager->registerEvents(new MedKit(), $plugin);
        $pluginManager->registerEvents(new ItemSullCrat(), $plugin);
        $pluginManager->registerEvents(new ItemPortableMague(), $plugin);

        $entityFactory = EntityFactory::getInstance();
        $entityFactory->register(SullCratEntity::class, function (World $world, CompoundTag $nbt): SullCratEntity {
            return new SullCratEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['SullCratEntity']);

        $entityFactory->register(SwitcherEntity::class, function (World $world, CompoundTag $nbt): SwitcherEntity {
            return new SwitcherEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['SwitcherEntity']);

        $entityFactory->register(BallOfRangeEntity::class, function (World $world, CompoundTag $nbt): BallOfRangeEntity {
            return new BallOfRangeEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['BallOfRangeEntity']);

        $entityFactory->register(PortableBardEntity::class, function(World $world, CompoundTag $nbt): PortableBardEntity{
            return new PortableBardEntity(EntityDataHelper::parseLocation($nbt,$world));
        },["PortableBardEntity"]);

        $entityFactory->register(PortableMagueEntity::class, function (World $world, CompoundTag $nbt): PortableMagueEntity {
            return new PortableMagueEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['PortableMagueEntity']);
    }

    public function onAbilityUse(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;
        $koth = HCFLoader::getInstance()->getKothManager()->getKothByPlayer($player);
        if($koth !== null && !$koth->areAbilitiesEnabled()){
            $event->cancel();
            $player->sendTitle("§cYou cannot use abilities in this KOTH!", "", 20, 40, 20);
        }
    }
}