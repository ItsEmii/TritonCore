<?php

declare(strict_types=1);

namespace hcf\handler\koth\command;

use hcf\handler\koth\command\subcommand\ClaimSubCommand;
use hcf\handler\koth\command\subcommand\CreateSubCommand;
use hcf\handler\koth\command\subcommand\DeleteSubCommand;
use hcf\handler\koth\command\subcommand\EditSubCommand;
use hcf\handler\koth\command\subcommand\ListSubCommand;
use hcf\handler\koth\command\subcommand\SetCapzoneSubCommand;
use hcf\handler\koth\command\subcommand\SetCoordsSubCommand;
use hcf\handler\koth\command\subcommand\SetPointsSubCommand;
use hcf\handler\koth\command\subcommand\StartSubCommand;
use hcf\handler\koth\command\subcommand\StopSubCommand;

use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KothCommand extends Command
{
    
    private array $subCommands = [];

    public function __construct()
    {
        parent::__construct('koth', 'Comandos de KoTH');
        $this->setPermission('koth.command');

        $this->subCommands = [
            'claim' => new ClaimSubCommand,
            'create' => new CreateSubCommand,
            'delete' => new DeleteSubCommand,
            'edit' => new EditSubCommand,
            'list' => new ListSubCommand,
            'setcapzone' => new SetCapzoneSubCommand,
            'setcoords' => new SetCoordsSubCommand,
            'setpoints' => new SetPointsSubCommand,
            'start' => new StartSubCommand,
        ];
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!isset($args[0])) {
            $sender->sendMessage("{$prefix}§7Lista de subcomandos disponibles:");
            $sender->sendMessage("§8- §e/koth claim");
            $sender->sendMessage("§8- §e/koth create <nombre>");
            $sender->sendMessage("§8- §e/koth delete <nombre>");
            $sender->sendMessage("§8- §e/koth edit <nombre>");
            $sender->sendMessage("§8- §e/koth list");
            $sender->sendMessage("§8- §e/koth setcapzone <nombre>");
            $sender->sendMessage("§8- §e/koth setcoords <nombre>");
            $sender->sendMessage("§8- §e/koth setpoints <nombre> <puntos>");
            $sender->sendMessage("§8- §e/koth start <nombre>");
            $sender->sendMessage("§8- §e/koth stop");
            return;
        }

        $subCommand = $this->subCommands[strtolower($args[0])] ?? null;

        if ($subCommand === null) {
            $sender->sendMessage("{$prefix}§cEse subcomando no existe.");
            return;
        }

        if ($args[0] !== 'list' && !$this->checkPermissionByCommand($sender, strtolower($args[0]))) {
            $sender->sendMessage("{$prefix}§cNo tienes permiso para usar este comando.");
            return;
        }

        array_shift($args);
        $subCommand->execute($sender, $args);
    }

    private function checkPermissionByCommand(CommandSender $player, string $command): bool
    {
        return $player->hasPermission('koth.command.' . $command);
    }
}