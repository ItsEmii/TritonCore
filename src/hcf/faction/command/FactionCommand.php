<?php

declare(strict_types=1);

namespace hcf\faction\command;

use hcf\faction\command\subcommand\AcceptInviteSubCommand;
use hcf\faction\command\subcommand\admin\ForceDisbandAllSubCommand;
use hcf\faction\command\subcommand\ListSubCommand;
use hcf\faction\command\subcommand\admin\ForceDisbandSubCommand;
use hcf\faction\command\subcommand\admin\SetDtrSubCommand;
use hcf\faction\command\subcommand\admin\SetPointsSubCommand;
use hcf\faction\command\subcommand\admin\SetRegenTimeSubCommand;
use hcf\faction\command\subcommand\admin\SetStrikesSubCommand;
use hcf\faction\command\subcommand\admin\ForceJoinFactionSubCommand;
use hcf\faction\command\subcommand\CampSubCommand;
use hcf\faction\command\subcommand\ChatSubCommand;
use hcf\faction\command\subcommand\CreateSubCommand;
use hcf\faction\command\subcommand\ClaimForSubCommand;
use hcf\faction\command\subcommand\ClaimSubCommand;
use hcf\faction\command\subcommand\DemoteSubCommand;
use hcf\faction\command\subcommand\DepositSubCommand;
use hcf\faction\command\subcommand\DisbandSubCommand;
use hcf\faction\command\subcommand\FocusSubCommand;
use hcf\faction\command\subcommand\HelpSubCommand;
use hcf\faction\command\subcommand\HomeSubCommand;
use hcf\faction\command\subcommand\InviteSubCommand;
use hcf\faction\command\subcommand\KickSubCommand;
use hcf\faction\command\subcommand\LeaveSubCommand;
use hcf\faction\command\subcommand\PromoteSubCommand;
use hcf\faction\command\subcommand\SetHomeSubCommand;
use hcf\faction\command\subcommand\StuckSubCommand;
use hcf\faction\command\subcommand\TopSubCommand;
use hcf\faction\command\subcommand\UnclaimSubCommand;
use hcf\faction\command\subcommand\UnfocusSubCommand;
use hcf\faction\command\subcommand\WhoSubCommand;
use hcf\faction\command\subcommand\WithdrawSubCommand;
use hcf\faction\command\subcommand\MapSubCommand;
use hcf\faction\command\subcommand\SetLeaderSubCommand;
use hcf\faction\command\subcommand\EChestSubCommand;
use hcf\faction\command\subcommand\HostageSubCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class FactionCommand extends Command
{
    /**
     * @var array<string, array{command: FactionSubCommand, name: string}>
     */
    private array $subCommands = [];

    public function __construct()
    {
        parent::__construct('f', 'Comandos para gestionar tu faction');
        $this->setAliases(['F']);
        $this->setPermission("use.player.command");

        $this->registerSubCommand('accept', new AcceptInviteSubCommand());
        $this->registerSubCommand('forcedisbandall', new ForceDisbandAllSubCommand());
        $this->registerSubCommand('join', new AcceptInviteSubCommand());
        $this->registerSubCommand('deposit', new DepositSubCommand());
        $this->registerSubCommand('d', new DepositSubCommand());
        $this->registerSubCommand('withdraw', new WithdrawSubCommand());
        $this->registerSubCommand('w', new WithdrawSubCommand());
        $this->registerSubCommand('create', new CreateSubCommand());
        $this->registerSubCommand('claimfor', new ClaimForSubCommand());
        $this->registerSubCommand('camp', new CampSubCommand());
        $this->registerSubCommand('claim', new ClaimSubCommand());
        $this->registerSubCommand('list', new ListSubCommand());
        $this->registerSubCommand('focus', new FocusSubCommand());
        $this->registerSubCommand('home', new HomeSubCommand());
        $this->registerSubCommand('hq', new HomeSubCommand());
        $this->registerSubCommand('sethome', new SetHomeSubCommand());
        $this->registerSubCommand('sethq', new SetHomeSubCommand());
        $this->registerSubCommand('stuck', new StuckSubCommand());
        $this->registerSubCommand('top', new TopSubCommand());
        $this->registerSubCommand('unfocus', new UnfocusSubCommand());
        $this->registerSubCommand('who', new WhoSubCommand());
        $this->registerSubCommand('invite', new InviteSubCommand());
        $this->registerSubCommand('disband', new DisbandSubCommand());
        $this->registerSubCommand('leave', new LeaveSubCommand());
        $this->registerSubCommand('kick', new KickSubCommand());
        $this->registerSubCommand('chat', new ChatSubCommand());
        $this->registerSubCommand('c', new ChatSubCommand());
        $this->registerSubCommand('unclaim', new UnclaimSubCommand());
        $this->registerSubCommand('info', new WhoSubCommand());
        $this->registerSubCommand('setdtr', new SetDtrSubCommand());
        $this->registerSubCommand('setpoints', new SetPointsSubCommand());
        $this->registerSubCommand('setregentime', new SetRegenTimeSubCommand());
        $this->registerSubCommand('forcedisband', new ForceDisbandSubCommand());
        $this->registerSubCommand('setstrikes', new SetStrikesSubCommand());
        $this->registerSubCommand('promote', new PromoteSubCommand());
        $this->registerSubCommand('setleader', new SetLeaderSubCommand());
        $this->registerSubCommand('demote', new DemoteSubCommand());
        $this->registerSubCommand('map', new MapSubCommand());
        $this->registerSubCommand('ec', new EChestSubCommand($this->getCommandsObjects()));
        $this->registerSubCommand('hostage', new HostageSubCommand());
        $this->registerSubCommand('forcej', new ForceJoinFactionSubCommand());
    }

    private function registerSubCommand(string $name, FactionSubCommand $command): void
    {
        $this->subCommands[strtolower($name)] = ['command' => $command, 'name' => $name];
    }

    private function getCommandsObjects(): array
    {
        return array_map(fn($data) => $data['command'], $this->subCommands);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!isset($args[0]) || $args[0] === "") {
            $prefix = "§7[§5Legends§7] ";
            $sender->sendMessage($prefix . "§eLista de subcomandos disponibles:");
            foreach ($this->subCommands as $key => $data) {
                $perm = method_exists($data['command'], 'getPermission') ? $data['command']->getPermission() : null;
                if ($perm === null || $sender->hasPermission($perm)) {
                    $desc = method_exists($data['command'], 'getDescription') ? $data['command']->getDescription() : "";
                    $sender->sendMessage($prefix . "§a/f " . $data['name'] . TextFormat::GRAY . ($desc !== "" ? " - " . TextFormat::WHITE . $desc : ""));
                }
            }
            return;
        }

        $sub = strtolower($args[0]);
        if (!isset($this->subCommands[$sub])) {
            $sender->sendMessage("§7[§5Legends§7] §cEl subcomando §f'{$args[0]}'§c no existe o no tienes permiso para usarlo.");
            return;
        }

        $subCommand = $this->subCommands[$sub]['command'];
        $perm = method_exists($subCommand, 'getPermission') ? $subCommand->getPermission() : null;
        if ($perm !== null && !$sender->hasPermission($perm)) {
            $sender->sendMessage("§7[§5Legends§7] §cNo tienes permiso para usar este subcomando.");
            return;
        }

        array_shift($args);
        $subCommand->execute($sender, $args);
    }

    public function onTabComplete(CommandSender $sender, string $alias, array $args): array
    {
        if (count($args) === 1) {
            $subs = [];
            foreach ($this->subCommands as $key => $data) {
                $perm = method_exists($data['command'], 'getPermission') ? $data['command']->getPermission() : null;
                if ($perm === null || $sender->hasPermission($perm)) {
                    $subs[] = $data['name'];
                }
            }
            return $subs;
        }
        return [];
    }
}