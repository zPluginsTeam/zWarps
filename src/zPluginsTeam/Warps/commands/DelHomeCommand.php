<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class DelHomeCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("delhome", "Delete a home", "/delhome <name>");
        $this->setPermission("warps.home.delete");
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
            $sender->sendMessage($this->plugin->getMessage("usage-delhome"));
            return false;
        }

        $homeName = $args[0];
        
        if (!$this->plugin->getDataManager()->deleteHome($sender, $homeName)) {
            $sender->sendMessage($this->plugin->getMessage("home-not-found", ["{home}" => $homeName]));
            return false;
        }

        $sender->sendMessage($this->plugin->getMessage("home-deleted", ["{home}" => $homeName]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
