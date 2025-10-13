<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\player\Player;
use hcf\utils\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SotwCommand extends Command
{
    public function __construct()
    {
        parent::__construct("sotw", "Comando para manejar el SOTW");
        $this->setPermission("op.cmd");
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        $sotw = HCFLoader::getInstance()->getTimerManager()->getSotw();

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize("&cUsa /sotw help"));
            return;
        }

        switch (strtolower($args[0])) {
            case "help":
                $sender->sendMessage(TextFormat::colorize("&eComandos de SOTW:\n") .
                    "&7/sotw start [tiempo] &8- &eInicia el SOTW\n" .
                    "&7/sotw stop &8- &eDetiene el SOTW\n" .
                    "&7/sotw off &8- &eDesactiva el SOTW solo para ti");
                break;

            case "start":
                if (!$sender->hasPermission("op.cmd")) return;

                if ($sotw->isActive()) {
                    $sender->sendMessage(TextFormat::colorize("&cEl SOTW ya está activo."));
                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::colorize("&cUsa /sotw start [tiempo]"));
                    return;
                }

                $time = Timer::time($args[1]);
                $sotw->setTime($time);
                $sotw->setActive(true);

                $sender->sendMessage(TextFormat::colorize("&a¡El SOTW ha comenzado por " . $args[1] . "!"));

                $console = new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage());
                Server::getInstance()->dispatchCommand($console, "keyall start 35m");
                Server::getInstance()->dispatchCommand($console, "keyallop start 45m");
                Server::getInstance()->dispatchCommand($console, "pkgall start 35m");
                break;

            case "stop":
                if (!$sender->hasPermission("op.cmd")) return;

                if (!$sotw->isActive()) {
                    $sender->sendMessage(TextFormat::colorize("&cEl SOTW no está activo."));
                    return;
                }

                $sotw->setActive(false);
                $sotw->clearDisabled();
                $sender->sendMessage(TextFormat::colorize("&cHas desactivado el SOTW manualmente."));
                break;

            case "off":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Este comando solo puede ser usado en el juego.");
                    return;
                }

                if (!$sender->hasPermission("use.player.command")) {
                    $sender->sendMessage(TextFormat::colorize("&cNo tienes permiso para usar este comando."));
                    return;
                }

                if (!$sotw->isActive()) {
                    $sender->sendMessage(TextFormat::colorize("&cEl SOTW no está activo."));
                    return;
                }

                if ($sotw->isDisabled($sender)) {
                    $sender->sendMessage(TextFormat::colorize("&eYa habías desactivado tu SOTW."));
                    return;
                }

                $sotw->setDisabled($sender);
                $sender->setAllowFlight(false);
                $sender->setFlying(false);
                $sender->sendMessage(TextFormat::colorize("&cHas desactivado tu protección de SOTW. Ahora puedes hacer PvP y también recibir daño."));
                break;

            default:
                $sender->sendMessage(TextFormat::colorize("&cUsa /sotw help"));
                break;
        }
    }
}