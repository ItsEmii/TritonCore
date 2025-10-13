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
class KeyallopCommand extends Command
{

    /**
     * SotwCommand construct.
     */
    public function __construct()
    {
        parent::__construct('keyallop', 'Command for keyallop');
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
                    TextFormat::colorize('&eKeyallOP Commands') . "\n" .
                    TextFormat::colorize('&7/keyallop start [time] - &eUse this command to start the keyallop') . "\n" .
                    TextFormat::colorize('&7/keyallop stop - &eUse this command to stop keyallop')
                );
                break;

            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getKeyAllOP()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe keyallop is already started'));
                    return;
                }

                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /keyallop start [time]'));
                    return;
                }
                $time = $args[1];

                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getKeyAllOP()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getKeyAllOP()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe keyallop has started!'));
                break;

            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getKeyAllOP()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe keyallop has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getKeyAllOP()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the keyallop'));
                break;

            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /keyallop help'));
                break;
        }
    }
}