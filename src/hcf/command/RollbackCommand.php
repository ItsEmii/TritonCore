<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\item\Item;
use pocketmine\item\Armor;

class RollbackCommand extends Command {

    private string $webhookURL = "";

    public function __construct() {
        parent::__construct("rb", "Rollback de inventario", "/rb <player>");
        $this->setPermission("op.cmd");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        $prefix = "§e[§5Legends§e] ";

        if (!$sender instanceof Player) {
            $sender->sendMessage($prefix . "§cEste comando solo puede usarse en el juego.");
            return true;
        }
        if (!$sender->hasPermission("op.cmd") && !$sender->isOp()) {
            $sender->sendMessage($prefix . TextFormat::RED . "No tienes permiso para usar este comando.");
            return true;
        }

        if (!isset($args[0])) {
            $sender->sendMessage($prefix . "§cUso correcto: /rb <player>");
            return true;
        }

        $data = new \pocketmine\utils\Config(HCFLoader::getInstance()->getDataFolder() . "rollbackdata.yml", \pocketmine\utils\Config::YAML);

        $targetPlayerName = $args[0];
        $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetPlayerName);
        if ($targetPlayer === null) {
            $sender->sendMessage($prefix . "§cEl jugador §e" . $targetPlayerName . " §cno está en línea.");
            return true;
        }
        $this->openDeathForm($sender, $targetPlayerName, $data);

        return true;
    }

    private function openDeathForm(Player $sender, string $target, \pocketmine\utils\Config $data): void {
        $prefix = "§e[§5Legends§e] ";

        $deaths = $data->get($target, []);
        if (empty($deaths)) {
            $sender->sendMessage($prefix . "§cNo se encontraron registros de muertes para §e$target§c.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $dataIndex) use ($deaths, $target, $data) {
            if ($dataIndex !== null) {
                $deathId = array_keys($deaths)[$dataIndex];
                $this->openReasonForm($player, $target, $deathId, $data);
            }
        });
        $form->setTitle("§l§5Legends §r§8Rollback");
        $form->setContent("§8[§l§c!§r§8] §eSelecciona un rollback para restaurar inventario\n");
        foreach ($deaths as $id => $death) {
            $killer = $death["killer"] !== "Unknown" ? "§l§eJugador§r§7:§4 " . $death["killer"] : "§l§5Causa§r§7:§d " . $death["cause"];
            $form->addButton("§l§6ID§r§7:§c $id §8| $killer");
        }
        $sender->sendForm($form);
    }

    private function openReasonForm(Player $player, string $target, string $deathId, \pocketmine\utils\Config $data): void {
        $prefix = "§e[§5Legends§e] ";
        $form = new CustomForm(function (Player $p, ?array $result) use ($target, $deathId, $data, $prefix) {
            if ($result === null) return;
            $reason = trim($result[0] ?? "");
            if ($reason === "") {
                $p->sendMessage($prefix . "§cDebes escribir una razón para el rollback.");
                return;
            }
            $this->doRollback($p, $target, $deathId, $data, $reason);
        });
        $form->setTitle("§l§5Legends §r§8Rollback Razón");
        $form->addInput("Escribe la razón del rollback:");
        $player->sendForm($form);
    }

    private function doRollback(Player $staff, string $targetName, string $deathId, \pocketmine\utils\Config $data, string $reason): void {
        $prefix = "§e[§5Legends§e] ";

        $deaths = $data->get($targetName, []);
        if (!isset($deaths[$deathId])) {
            $staff->sendMessage($prefix . "§cRollback no encontrado.");
            return;
        }
        $targetPlayer = HCFLoader::getInstance()->getServer()->getPlayerExact($targetName);
        if ($targetPlayer === null) {
            $staff->sendMessage($prefix . "§cEl jugador §e" . $targetName . " §cno está en línea.");
            return;
        }
        $items = $deaths[$deathId]["items"];
        $nbtSerializer = new BigEndianNbtSerializer();
        foreach ($items as $itemData) {
            try {
                $compoundTag = $nbtSerializer->read(base64_decode($itemData))->mustGetCompoundTag();
                $item = Item::nbtDeserialize($compoundTag);
                $this->addItemToPlayer($targetPlayer, $item);
            } catch (\Throwable $e) {
                $staff->sendMessage($prefix . "§8[§l§c!§r§8]§c Error al restaurar item: §e" . $e->getMessage());
            }
        }

        unset($deaths[$deathId]);
        $data->set($targetName, $deaths);
        $data->save();

        $staff->sendMessage($prefix . "§8[§l§a!§r§8]§a Rollback realizado correctamente.");

        $this->sendDiscordWebhook(
            $this->webhookURL,
            "**rollback**\n".
            "**staff:** {$staff->getName()}\n".
            "**user:** {$targetName}\n".
            "**fecha y hora:** ".date("Y-m-d H:i:s")."\n".
            "**razón:** {$reason}"
        );
    }

    private function addItemToPlayer(Player $player, Item $item): void {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();

        if ($item instanceof Armor) {
            $armorSlot = $item->getArmorSlot();
            $currentArmor = $armorInventory->getItem($armorSlot);
            if ($currentArmor->isNull()) {
                $armorInventory->setItem($armorSlot, $item);
                return;
            }
        }
        if ($inventory->canAddItem($item)) {
            $inventory->addItem($item);
        } else {
            $player->getWorld()->dropItem($player->getPosition(), $item);
        }
    }

    private function sendDiscordWebhook(string $webhookURL, string $content): void {
        $data = json_encode([
            "content" => $content
        ]);
        $ch = curl_init($webhookURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}