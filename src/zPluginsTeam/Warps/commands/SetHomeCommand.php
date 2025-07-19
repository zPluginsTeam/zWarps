<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use zPluginsTeam\Warps\Main;

class SetHomeCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("sethome", "Set a home at your current location", "/sethome [name]");
        $this->setPermission("warps.home.set");
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

        $homeName = $args[0] ?? "default";
        
        if (!$this->plugin->getDataManager()->setHome($sender, $homeName)) {
            $sender->sendMessage($this->plugin->getMessage("max-homes-reached"));
            return false;
        }

        $sender->sendMessage($this->plugin->getMessage("home-set", ["{home}" => $homeName]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
