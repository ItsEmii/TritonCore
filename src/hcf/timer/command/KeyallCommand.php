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
class KeyallCommand extends Command
{

    /**
     * SotwCommand construct.
     */
    public function __construct()
    {
        parent::__construct('keyall', 'Command for keyall');
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
                    TextFormat::colorize('&eKeyall Commands') . "\n" .
                    TextFormat::colorize('&7/keyall start [time] - &eUse this command to start the keyall') . "\n" .
                    TextFormat::colorize('&7/keyall stop - &eUse this command to stop keyall')
                );
                break;

            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getKeyAll()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe keyall is already started'));
                    return;
                }

                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /keyall start [time]'));
                    return;
                }
                $time = $args[1];

                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getKeyAll()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getKeyAll()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe keyall has started!'));
                break;

            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getKeyAll()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe keyall has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getKeyAll()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the keyall'));
                break;

            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /keyall help'));
                break;
        }
    }
}