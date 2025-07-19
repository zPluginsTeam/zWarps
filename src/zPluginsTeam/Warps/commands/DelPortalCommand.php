<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class DelPortalCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("delportal", "Delete a portal", "/delportal <name>");
        $this->setPermission("warps.portal.delete");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessage("usage-delportal"));
            return false;
        }

        $portalName = $args[0];
        
        if (!$this->plugin->getDataManager()->deletePortal($portalName)) {
            $sender->sendMessage($this->plugin->getMessage("portal-not-found", ["{portal}" => $portalName]));
            return false;
        }
        
        $sender->sendMessage($this->plugin->getMessage("portal-deleted", ["{portal}" => $portalName]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
