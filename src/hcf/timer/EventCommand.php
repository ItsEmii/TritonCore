<?php

namespace hcf\timer;

use hcf\player\Player;
use hcf\timer\form\EventForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\utils\TextFormat;

class EventCommand extends Command
{
    public function __construct()
    {
        parent::__construct("event", "Use for send form to activate one event");
        $this->setPermission("op.cmd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize("&cUse this command in game"));
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage(TextFormat::colorize("&cYou don`t have permission to use this command"));
            return;
        }

        $sender->sendForm(new EventForm());
    }
}