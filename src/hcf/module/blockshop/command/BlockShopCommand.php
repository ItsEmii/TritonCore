<?php

declare(strict_types=1);

namespace hcf\module\blockshop\command;

use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use hcf\entity\npcs\{InfoEntity, TopFactionsEntity, TopKDREntity, TopKillsEntity};
use hcf\module\blockshop\entity\ShopAndSellEntity;
use hcf\module\coinshop\entity\CoinShopEntity;
use hcf\module\blockshop\utils\Utils as Utilshop;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\enchantment\EnchantmentInstance;

class BlockShopCommand extends Command
{
    public function __construct()
    {
        parent::__construct("npc", "Abrir interfaz de gestión de NPCs.");
        $this->setPermission("op.cmd");
    }

    public function execute(CommandSender $sender, string $label, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Usa este comando dentro del juego.");
            return;
        }

        $this->sendNpcGui($sender);
    }

    private function sendNpcGui(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;

            switch ($data) {
                case 0: // NPC Tienda
                    $entity = new ShopAndSellEntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §eTienda §a creado exitosamente!");
                    break;

                case 1: // NPC Información
                    $entity = new InfoEntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §bInformación §a creado exitosamente!");
                    break;

                case 2: // NPC Top Kills
                    $entity = new TopKillsEntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §9Top Kills §a creado exitosamente!");
                    break;

                case 3: // NPC Top KDR
                    $entity = new TopKDREntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §3Top KDR §a creado exitosamente!");
                    break;

                case 4: // NPC Top Factions
                    $entity = new TopFactionsEntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §2Top Factions §a creado exitosamente!");
                    break;

                case 5: // NPC CoinShop
                    $entity = new CoinShopEntity($player->getLocation(), $player->getSkin(), Utilshop::createBasicNBT($player));
                    $entity->spawnToAll();
                    $player->sendMessage("§aNPC §6CoinShop §a creado exitosamente!");
                    break;

                case 6: // Herramienta para eliminar NPCs
                    $hoe = VanillaItems::GOLDEN_HOE()
                        ->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FEATHER_FALLING(), 1))
                        ->setCustomName("§eHerramienta para eliminar NPC §7(Clic derecho)");
                    $player->getInventory()->addItem($hoe);
                    $player->sendMessage("§aHas recibido la §eHerramienta para eliminar NPC!");
                    break;
            }
        });

        $form->setTitle("§l§6Gestión de NPCs");
        $form->addButton("§cCrear NPC Tienda\n§7Generar un NPC comerciante");
        $form->addButton("§bCrear NPC Información\n§7Mostrar información del servidor");
        $form->addButton("§9Crear NPC Top Kills\n§7Tabla de kills");
        $form->addButton("§3Crear NPC Top KDR\n§7Tabla de KDR");
        $form->addButton("§2Crear NPC Top Factions\n§7Tabla de facciones");
        $form->addButton("§6Crear NPC CoinShop\n§7Acceder al sistema CoinShop");
        $form->addButton("§eObtener herramienta para eliminar NPC\n§7Clic derecho en un NPC para eliminar");

        $player->sendForm($form);
    }
}