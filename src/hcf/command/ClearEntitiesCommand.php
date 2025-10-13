<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\entity\TextEntity;
use hcf\entity\CustomItemEntity;
use hcf\entity\server\KitEntity;
use hcf\player\Player;
use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ClearEntitiesCommand extends Command
{
    public function __construct()
    {
        parent::__construct('clearentities', '§hLimpia entidades que no sean jugadores');
        $this->setPermission('op.cmd');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $prefix = HCFLoader::$prefix;

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . TextFormat::RED . "Este comando solo puede usarlo un jugador.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }

        $count = 0;

        foreach ($sender->getWorld()->getEntities() as $entity) {
            if (
                !($entity instanceof TextEntity) &&
                !($entity instanceof CustomItemEntity) &&
                !($entity instanceof Player) &&
                !($entity instanceof KitEntity)
            ) {
                $entity->flagForDespawn();
                $count++;
            }
        }

        $sender->sendMessage($prefix . TextFormat::GREEN . "Entidades eliminadas: $count");
    }
}