<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PvPCommand extends Command
{
    public function __construct()
    {
        parent::__construct('pvp', '§hUsa este comando para activar o manipular el PvP');
        $this->setPermission("use.player.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) return;

        if (count($args) < 1) {
            $sender->sendMessage($prefix . TextFormat::RED . "Uso: /pvp enable");
            return;
        }

        $subcommand = strtolower($args[0]);

        switch ($subcommand) {
            case 'enable':
                if ($sender->getSession()->getCooldown('starting.timer') === null && $sender->getSession()->getCooldown('pvp.timer') === null) {
                    $sender->sendMessage($prefix . TextFormat::RED . "No tienes starting timer ni PvP timer.");
                    return;
                }

                if ($sender->getSession()->getCooldown('starting.timer') !== null) {
                    $sender->getSession()->removeCooldown('starting.timer');
                }

                if ($sender->getSession()->getCooldown('pvp.timer') !== null) {
                    $sender->getSession()->removeCooldown('pvp.timer');
                }

                $sender->getSession()->addCooldown('sotw.pvp', '', 3600, false, false);

                if (HCFLoader::getInstance()->getTimerManager()->getSotw()->isActive()) {
                    $sender->sendMessage($prefix . TextFormat::GREEN . "Has desactivado tu SOTWTimer.");
                } else {
                    $sender->sendMessage($prefix . TextFormat::GREEN . "Has desactivado tu PvPTimer.");
                }
                break;

            case 'force':
                if (!$sender->hasPermission('op.cmd')) {
                    $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
                    return;
                }

                if ($sender->getSession()->getCooldown('pvp.timer') !== null) {
                    $sender->getSession()->removeCooldown('pvp.timer');
                    $sender->sendMessage($prefix . TextFormat::GREEN . "PvP timer eliminado forzosamente.");
                } else {
                    $sender->sendMessage($prefix . TextFormat::YELLOW . "No tienes PvP timer activo.");
                }
                break;

            case 'add':
                $sender->getSession()->addCooldown('pvp.timer', '', 3600, false, false);
                $sender->sendMessage($prefix . TextFormat::GREEN . "PvP timer agregado por 1 hora.");
                break;

            default:
                $sender->sendMessage($prefix . TextFormat::RED . "Usa: /pvp enable");
                break;
        }
    }
}