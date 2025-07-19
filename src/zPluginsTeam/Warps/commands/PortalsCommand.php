<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;
use zPluginsTeam\Warps\forms\PortalsForm;

class PortalsCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("portals", "List all portals or open admin form", "/portals");
        $this->setPermission("warps.portal.list");
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
        if ($sender->hasPermission("warps.portal.admin")) {
            $form = new PortalsForm($this->plugin);
            $form->sendForm($sender);
            return true;
        }

        // Otherwise, list portals
        $portals = $this->plugin->getDataManager()->getPortals();
        $enabledPortals = [];
        
        foreach ($portals as $name => $data) {
            if ($data['enabled']) {
                // Check if player has permission for this portal
                if (empty($data['permissions'])) {
                    $enabledPortals[] = $name;
                } else {
                    foreach ($data['permissions'] as $permission) {
                        if ($sender->hasPermission($permission)) {
                            $enabledPortals[] = $name;
                            break;
                        }
                    }
                }
            }
        }
        
        if (empty($enabledPortals)) {
            $sender->sendMessage($this->plugin->getMessage("no-portals"));
            return true;
        }

        $portalList = implode(", ", $enabledPortals);
        $sender->sendMessage($this->plugin->getMessage("portals-list", ["{portals}" => $portalList]));
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
