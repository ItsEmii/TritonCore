<?php

namespace hcf\addons\modules;

use hcf\player\Player;
use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\Language;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class JoinCommand implements Listener
{
    public function onJoin(PlayerJoinEvent $ev): void
    {
        if(($p = $ev->getPlayer())->hasPlayedBefore()) return;
        $nick = "\"{$p->getName()}\"";
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), new Language(Language::FALLBACK_LANGUAGE)), "key give Starter 5 $nick");
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), new Language(Language::FALLBACK_LANGUAGE)), "key give Items 5 $nick");
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), new Language(Language::FALLBACK_LANGUAGE)), "key give Summer 5 $nick");

    }



}