<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class SetWarpCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("setwarp", "Set a warp at your current location", "/setwarp <name>");
        $this->setPermission("warps.warp.set");
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
            $sender->sendMessage($this->plugin->getMessage("usage-setwarp"));
            return false;
        }

        $warpName = $args[0];
        $this->plugin->getDataManager()->setWarp($warpName, $sender->getPosition());
        $sender->sendMessage($this->plugin->getMessage("warp-set", ["{warp}" => $warpName]));
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
