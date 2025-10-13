<?php

declare(strict_types=1);

namespace hcf\faction\command\subcommand;

use hcf\faction\command\FactionSubCommand;
use hcf\faction\Faction;
use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class CreateSubCommand implements FactionSubCommand
{

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function execute(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player)
            return;

        if ($sender->getSession()->getFaction() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa tienes una faction'));
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUsa /faction create [nombre]'));
            return;
        }
        $factionName = $args[0];

        if (!preg_match('/^[a-zA-Z0-9]+$/', $factionName)) {
            $sender->sendMessage(TextFormat::colorize('&cEl nombre de la faction solo puede llevar letras y números, sin espacios ni caracteres especiales.'));
            return;
        }

        if (HCFLoader::getInstance()->getFactionManager()->getFaction($factionName) !== null || HCFLoader::getInstance()->getClaimManager()->getClaim($factionName) !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYa existe una faction con ese nombre'));
            return;
        }

        if (strlen($factionName) < 4) {
            $sender->sendMessage(TextFormat::colorize('&c¡Tu faction debe tener más de 5 caracteres para crearla!'));
            return;
        }

        if (strlen($factionName) > 10) {
            $sender->sendMessage(TextFormat::colorize('&cEl nombre de tu faction no puede contener más de 10 caracteres'));
            return;
        }

        $checkName = explode(' ', $factionName);
        $checkName9 = explode('/', $factionName);

        if (count($checkName) > 1 || count($checkName9) > 1) {
            $sender->sendMessage(TextFormat::colorize('&cEl nombre de tu faction no puede contener espacios ni /'));
            return;
        }

        if (in_array($factionName, ['Spawn', 'Nether-Spawn', 'End-Spawn'])) {
            $sender->sendMessage(TextFormat::colorize('&cNombre inválido'));
            return;
        }
        HCFLoader::getInstance()->getFactionManager()->createFaction($factionName, [
            'roles' => [
                (string) $sender->getUniqueId() => Faction::LEADER
            ],
            'dtr' => 1.1,
            'balance' => 0,
            'points' => 0,
            'kothCaptures' => 0,
            'timeRegeneration' => 0,
            'home' => null,
            'claim' => null
        ]);
        $sender->getSession()->setFaction($factionName);
        $sender->sendMessage(TextFormat::colorize('&aHas creado la faction'));
        $sender->getServer()->broadcastMessage(TextFormat::colorize('&eLa faction  &9' . $factionName . ' &eha sido &acreado &epor &f' . $sender->getName()));
    }
}