<?php

declare(strict_types=1);

namespace hcf;

use CortexPE\Commando\PacketHooker;
use hcf\abilities\AbilitiesManager;
use hcf\addons\AddonsManager;
use hcf\block\BlockManager;
use hcf\claim\ClaimManager;
use hcf\command\CommandManager;
use hcf\entity\EntityManager;
use hcf\faction\FactionManager;
use hcf\handler\HandlerManager;
use hcf\item\ItemManager;
use hcf\handler\koth\KothManager;
use hcf\listener\{ClaimListener, DirectPickupListener, FactionListener, HCFListener};
use hcf\module\{ModuleManager, modules\ModulesManager, pkg\PackageHandler, pkg\util\Content, Potion};
use hcf\player\disconnected\{DisconnectedManager, LogoutMob};
use hcf\provider\Provider;
use hcf\player\session\SessionManager;
use hcf\timer\{TimerManager, types\TimerCustom};
use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\effect\{EffectInstance, VanillaEffects};
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;

class HCFLoader extends PluginBase {
    public static HCFLoader $instance;

    private Provider $provider;
    private EntityManager $entityManager;
    private AbilitiesManager $abilitiesManager;
    private ClaimManager $claimManager;
    private CommandManager $commandManager;
    private TimerManager $timerManager;
    private FactionManager $factionManager;
    private KothManager $kothManager;
    private DisconnectedManager $disconnectedManager;
    private SessionManager $sessionManager;
    private ItemManager $itemManager;
    private ModuleManager $moduleManager;
    private HandlerManager $handlerManager;
    private BlockManager $blockManager;

    public static array $enderPearl = [];
    public static array $bard_allow = [];
    public static array $mague_allow = [];
    public static array $archer_allow = [];
    public static string $prefix = "§e[§bTriton§e] §r";

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        $this->getLogger()->notice("§bTritonCore §qenabled");

        @mkdir($this->getDataFolder() . "others/");
        @mkdir($this->getDataFolder() . "kits/");

        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        if (!PacketHooker::isRegistered()) PacketHooker::register($this);

        $this->provider = new Provider();
        $this->entityManager = new EntityManager();
        $this->claimManager = new ClaimManager();
        $this->commandManager = new CommandManager();
        $this->timerManager = new TimerManager();
        $this->abilitiesManager = new AbilitiesManager();
        $this->factionManager = new FactionManager();
        $this->kothManager = new KothManager();
        $this->disconnectedManager = new DisconnectedManager();
        $this->sessionManager = new SessionManager();
        $this->itemManager = new ItemManager();
        $this->handlerManager = new HandlerManager();
        $this->moduleManager = new ModuleManager();
        $this->blockManager = new BlockManager();

        AddonsManager::init();
        ModulesManager::init();

        foreach ([
            new HCFListener(),
            new Potion(),
            new PackageHandler($this),
            new DirectPickupListener(),
            new ClaimListener(),
            new FactionListener()
        ] as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        $this->getServer()->getNetwork()->setName(TextFormat::colorize($this->getConfig()->get("motd", "Default HCF Server")));

        foreach (["me", "kill", "about", "suicide"] as $cmd) {
            $command = $this->getServer()->getCommandMap()->getCommand($cmd);
            if ($command !== null) $this->getServer()->getCommandMap()->unregister($command);
        }

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $kothName = $this->getKothManager()->getKothActive();
            if ($kothName !== null) {
                $koth = $this->getKothManager()->getKoth($kothName);
                if ($koth !== null) $koth->update();
                else $this->getKothManager()->setKothActive(null);
            }

            if ($this->getTimerManager()->getPurge()->isActive()) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 7, 1));
                }
            }

            $this->getTimerManager()->getSotw()->update();
            $this->getTimerManager()->getEotw()->update();
            $this->getTimerManager()->getPurge()->update();
            $this->getTimerManager()->getPoints()->update();
            $this->getTimerManager()->getKeyAll()->update();
            $this->getTimerManager()->getKeyAllOP()->update();
            $this->getTimerManager()->getPackages()->update();

            foreach ($this->getTimerManager()->getCustomTimers() as $name => $timer) {
                if ($timer instanceof TimerCustom) $timer->update();
            }

            foreach ($this->getServer()->getWorldManager()->getDefaultWorld()->getEntities() as $entity) {
                if ($entity instanceof LogoutMob) {
                    $entity->onUpdate(20);
                }
            }

            foreach ($this->getSessionManager()->getSessions() as $session) {
                $session->onUpdate();
            }

            foreach ($this->getFactionManager()->getFactions() as $faction) {
                $faction->onUpdate();
            }

        }), 20);
    }

    protected function onDisable(): void {
        Content::getInstance()->save();
        $this->getProvider()->save();
        $this->disconnectedManager->onDisable();

        $this->getLogger()->notice("§bTritonCore §4disabled");

        foreach ($this->getServer()->getOnlinePlayers() as $player) {

        }
    }

    public static function getInstance(): HCFLoader {
        return self::$instance;
    }

    public function getProvider(): Provider {
        return $this->provider;
    }

    public function getEntityManager(): EntityManager {
        return $this->entityManager;
    }

    public function getClaimManager(): ClaimManager {
        return $this->claimManager;
    }

    public function getCommandManager(): CommandManager {
        return $this->commandManager;
    }

    public function getTimerManager(): TimerManager {
        return $this->timerManager;
    }

    public function getAbilitiesManager(): AbilitiesManager {
        return $this->abilitiesManager;
    }

    public function getFactionManager(): FactionManager {
        return $this->factionManager;
    }

    public function getKothManager(): KothManager {
        return $this->kothManager;
    }

    public function getDisconnectedManager(): DisconnectedManager {
        return $this->disconnectedManager;
    }

    public function getSessionManager(): SessionManager {
        return $this->sessionManager;
    }

    public function getItemManager(): ItemManager {
        return $this->itemManager;
    }

    public function getModuleManager(): ModuleManager {
        return $this->moduleManager;
    }

    public function getHandlerManager(): HandlerManager {
        return $this->handlerManager;
    }

    public function getBlockManager(): BlockManager {
        return $this->blockManager;
    }
}