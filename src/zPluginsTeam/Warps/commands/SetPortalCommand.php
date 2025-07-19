<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class SetPortalCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;
    private static array $portalSetup = [];

    public function __construct(Main $plugin) {
        parent::__construct("setportal", "Set a portal between two locations", "/setportal <name>");
        $this->setPermission("warps.portal.set");
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
            $sender->sendMessage($this->plugin->getMessage("usage-setportal"));
            return false;
        }

        $portalName = $args[0];
        $playerName = $sender->getName();

        if (!isset(self::$portalSetup[$playerName])) {
            self::$portalSetup[$playerName] = [];
        }

        if (!isset(self::$portalSetup[$playerName][$portalName])) {
            // First position
            $this->plugin->getDataManager()->setPortalPosition($portalName, $sender->getPosition(), 1);
            self::$portalSetup[$playerName][$portalName] = 1;
            $sender->sendMessage($this->plugin->getMessage("portal-pos1-set", ["{portal}" => $portalName]));
        } elseif (self::$portalSetup[$playerName][$portalName] === 1) {
            // Second position
            $isComplete = $this->plugin->getDataManager()->setPortalPosition($portalName, $sender->getPosition(), 2);
            if ($isComplete) {
                self::$portalSetup[$playerName][$portalName] = 2;
                $sender->sendMessage($this->plugin->getMessage("portal-pos2-set", ["{portal}" => $portalName]));
                $sender->sendMessage($this->plugin->getMessage("portal-set-destination", ["{portal}" => $portalName]));
            }
        } elseif (self::$portalSetup[$playerName][$portalName] === 2) {
            // Set destination
            $this->plugin->getDataManager()->setPortalDestination($portalName, $sender->getPosition());
            unset(self::$portalSetup[$playerName][$portalName]);
            
            $portalData = $this->plugin->getDataManager()->getPortal($portalName);
            
            $sender->sendMessage($this->plugin->getMessage("portal-created", ["{portal}" => $portalName]));
        }
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
