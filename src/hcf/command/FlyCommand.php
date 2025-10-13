<?php

namespace hcf\command;

use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class FlyCommand extends Command implements Listener
{
    private Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        parent::__construct("fly", "Activa o desactiva el Fly");
        $this->setPermission("fly.command");
        $this->plugin = $plugin;

        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo puede usarse en el juego.");
            return;
        }

        $sotw = HCFLoader::getInstance()->getTimerManager()->getSotw();

        if (!$sotw->isActive() || $sotw->isDisabled($sender)) {
            if ($sender->isFlying()) {
                $sender->setFlying(false);
                $sender->setAllowFlight(false);
            }
            $sender->sendMessage(TextFormat::RED . "No puedes usar /fly. El SOTW no está activo o ya lo desactivaste.");
            return;
        }

        if ($sender->isFlying()) {
            $sender->setFlying(false);
            $sender->setAllowFlight(false);
            $sender->sendMessage(TextFormat::colorize("&cHas desactivado el Fly."));
        } else {
            $sender->setAllowFlight(true);
            $sender->setFlying(true);
            $sender->sendMessage(TextFormat::colorize("&aHas activado el Fly."));
        }
    }

    public function onFallDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Player) return;
        if ($event->getCause() !== EntityDamageEvent::CAUSE_FALL) return;

        $sotw = HCFLoader::getInstance()->getTimerManager()->getSotw();

        if ($sotw->isActive() && !$sotw->isDisabled($entity) && $entity->isFlying()) {
            $event->cancel();
        }
    }
}