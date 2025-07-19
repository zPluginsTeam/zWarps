<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class DelWarpCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("delwarp", "Delete a warp", "/delwarp <name>");
        $this->setPermission("warps.warp.delete");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessage("usage-delwarp"));
            return false;
        }

        $warpName = $args[0];
        
        if (!$this->plugin->getDataManager()->deleteWarp($warpName)) {
            $sender->sendMessage($this->plugin->getMessage("warp-not-found", ["{warp}" => $warpName]));
            return false;
        }

        $sender->sendMessage($this->plugin->getMessage("warp-deleted", ["{warp}" => $warpName]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
