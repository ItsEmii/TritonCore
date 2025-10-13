<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class x2PointsCommand
 * @package hcf\timer\command
 */
class x2PointsCommand extends Command
{
    
    /**
     * x2PointsCommand construct.
     */
    public function __construct()
    {
        parent::__construct('x2points', 'Command for event x2oints');
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
            $sender->sendMessage(TextFormat::colorize('&cUse /x2points help'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&ex2points Commands') . "\n" .
                    TextFormat::colorize('&7/x2points start [time] - &eUse this command to start the x2points') . "\n" .
                    TextFormat::colorize('&7/x2points stop - &eUse this command to stop x2points')
                );
                break;
            
            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getPoints()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe x2points is already started'));
                    return;
                }
                
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /x2points start [time]'));
                    return;
                }
                $time = $args[1];
                
                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getPoints()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getPoints()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe x2points has started!'));
                break;
            
            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getPoints()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe x2points has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getPoints()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the x2points'));
                break;
            
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /x2points help'));
                break;
        }
    }
}