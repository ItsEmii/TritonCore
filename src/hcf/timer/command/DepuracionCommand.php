<?php

declare(strict_types=1);

namespace hcf\timer\command;

use hcf\HCFLoader;
use hcf\utils\time\Timer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Class DepuracionCommand
 * @package hcf\timer\command
 */
class DepuracionCommand extends Command
{
    
    /**
     * DepuracionCommand construct.
     */
    public function __construct()
    {
        parent::__construct('depuracion', 'Command for event x2oints');
        $this->setPermission('op.cmx');
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
            $sender->sendMessage(TextFormat::colorize('&cUse /depuracion help'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'help':
                $sender->sendMessage(
                    TextFormat::colorize('&eDepuracion Commands') . "\n" .
                    TextFormat::colorize('&7/depuracion start [time] - &eUse this command to start the depuracion') . "\n" .
                    TextFormat::colorize('&7/depuracion stop - &eUse this command to stop depuracion')
                );
                break;
            
            case 'start':
                if (HCFLoader::getInstance()->getTimerManager()->getDepuracion()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe depuracion is already started'));
                    return;
                }
                
                if (count($args) < 2) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /depuracion start [time]'));
                    return;
                }
                $time = $args[1];
                
                $time = Timer::time($time);
                HCFLoader::getInstance()->getTimerManager()->getDepuracion()->setActive(true);
                HCFLoader::getInstance()->getTimerManager()->getDepuracion()->setTime((int) $time);
                $sender->sendMessage(TextFormat::colorize('&aThe depuracion has started!'));
                break;
            
            case 'stop':
                if (!HCFLoader::getInstance()->getTimerManager()->getDepuracion()->isActive()) {
                    $sender->sendMessage(TextFormat::colorize('&cThe depuracion has not started'));
                    return;
                }
                HCFLoader::getInstance()->getTimerManager()->getDepuracion()->setActive(false);
                $sender->sendMessage(TextFormat::colorize('&cYou have turned off the depuracion'));
                break;
            
            default:
                $sender->sendMessage(TextFormat::colorize('&cUse /depuracion help'));
                break;
        }
    }
}