<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\world\Position;
use zPluginsTeam\Warps\Main;

class WarpCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("warp", "Teleport to a warp", "/warp <name>");
        $this->setPermission("warps.warp");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("player-only"));
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessage("usage-warp"));
            return false;
        }

        $warpName = $args[0];
        $warpData = $this->plugin->getDataManager()->getWarp($warpName);

        if ($warpData === null) {
            $sender->sendMessage($this->plugin->getMessage("warp-not-found", ["{warp}" => $warpName]));
            return false;
        }

        if (!($warpData["enabled"] ?? true)) {
            $sender->sendMessage($this->plugin->getMessage("warp-disabled", ["{warp}" => $warpName]));
            return false;
        }

        // Check permission(s) to access this warp
        if (!empty($warpData["permissions"])) {
            $hasPermission = false;
            foreach ($warpData["permissions"] as $perm) {
                if ($sender->hasPermission($perm)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                $sender->sendMessage($this->plugin->getMessage("no-warp-permission", ["{warp}" => $warpName]));
                return false;
            }
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($warpData["world"]);
        if ($world === null) {
            $sender->sendMessage($this->plugin->getMessage("world-not-found", ["{world}" => $warpData["world"] ?? "unknown"]));
            return false;
        }

        $position = new Position($warpData["x"], $warpData["y"], $warpData["z"], $world);
        $sender->teleport($position);
        $sender->sendMessage($this->plugin->getMessage("warp-teleport", ["{warp}" => $warpName]));

        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
