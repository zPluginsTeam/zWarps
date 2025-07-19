<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class HomesCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("homes", "List all your homes", "/homes");
        $this->setPermission("warps.home.list");
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

        $homes = $this->plugin->getDataManager()->getPlayerHomes($sender);
        
        if (empty($homes)) {
            $sender->sendMessage($this->plugin->getMessage("no-homes"));
            return true;
        }

        $homeList = implode(", ", array_keys($homes));
        $sender->sendMessage($this->plugin->getMessage("homes-list", ["{homes}" => $homeList]));
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
