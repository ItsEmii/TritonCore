<?php

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListSubCommand implements FactionSubCommand
{

    /**
     * @inheritDoc
     */
    public function execute(CommandSender $sender, array $args): void
    {
        $all = HCFLoader::getInstance()->getFactionManager()->getFactions();
        $factions = array_filter($all, fn(Faction $faction) => !in_array($faction->getName(), ['Spawn', 'Nether-Spawn', 'End-Spawn'], true));

        if (count($factions) === 0) {
            $sender->sendMessage(TextFormat::colorize('&cNo hay factions.'));
            return;
        }
        $chunks = array_chunk($factions, 10);
        $page = 0;

        if (isset($args[0])) {
            if (!is_numeric($args[0])) {
                $sender->sendMessage(TextFormat::colorize('&cNúmero inválido.'));
                return;
            }
            $number = (int) $args[0];

            if ($number <= 0 || !isset($chunks[$number - 1])) {
                $sender->sendMessage(TextFormat::colorize('&cPágina inválida.'));
                return;
            }
            $page = $number - 1;
        }
        $sender->sendMessage(TextFormat::colorize('&aLista de factions &7[' . ($page + 1) . '/' . count($chunks) . ']'));
        $sender->sendMessage(TextFormat::colorize(implode(PHP_EOL, array_map(fn(Faction $faction) => '&a' . $faction->getName() . ' &7[' . count($faction->getOnlineMembers()) . '/' . count($faction->getMembers()) . ']', $chunks[$page]))));
    }
}