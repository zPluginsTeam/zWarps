<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;
use zPluginsTeam\Warps\forms\WarpsForm;

class WarpsCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("warps", "List all warps or open admin form", "/warps");
        $this->setPermission("warps.warp.list");
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

        // If player has admin permission, show admin form
        if ($sender->hasPermission("warps.warp.admin")) {
            $form = new WarpsForm($this->plugin);
            $form->sendForm($sender);
            return true;
        }

        // Otherwise, list warps
        $warps = $this->plugin->getDataManager()->getWarps();
        $enabledWarps = [];
        
        foreach ($warps as $name => $data) {
            if ($data['enabled']) {
                // Check if player has permission for this warp
                if (empty($data['permissions'])) {
                    $enabledWarps[] = $name;
                } else {
                    foreach ($data['permissions'] as $permission) {
                        if ($sender->hasPermission($permission)) {
                            $enabledWarps[] = $name;
                            break;
                        }
                    }
                }
            }
        }
        
        if (empty($enabledWarps)) {
            $sender->sendMessage($this->plugin->getMessage("no-warps"));
            return true;
        }

        $warpList = implode(", ", $enabledWarps);
        $sender->sendMessage($this->plugin->getMessage("warps-list", ["{warps}" => $warpList]));
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
