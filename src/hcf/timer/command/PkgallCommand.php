<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class SotwCommand
 * @package hcf\timer\command
 */
class PkgallCommand extends Command
{

    /**
     * SotwCommand construct.
     */
    public function __construct()
    {
        parent::__construct('pkgall', 'Command for pkgall');
        $this->setPermission('op.cmd');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($sender))
            return;

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize("&cUse /$commandLabel help"));
            return;
        }

        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&ePackagesAll Commands') . "\n" .
                    TextFormat::colorize('&7/pkgall start [time] - &eUse this command to start the pkgall') . "\n" .
                    TextFormat::colorize('&7/pkgall stop - &eUse this command to stop pkgall')
                );
                break;

            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getPackages()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe pkgall is already started'));
                    return;
                }

                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /pkgall start [time]'));
                    return;
                }
                $time = $args[1];

                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getPackages()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getPackages()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe pkgall has started!'));
                break;

            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getPackages()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe pkgall has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getPackages()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the pkgall'));
                break;

            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /pkgall help'));
                break;
        }
    }
}