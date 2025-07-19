<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\world\Position;
use zPluginsTeam\Warps\Main;

class HomeCommand extends Command {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("home", "Teleport to your home", "/home [name]");
        $this->setPermission("warps.home");
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
        $homeData = $this->plugin->getDataManager()->getHome($sender, $homeName);

        if ($homeData === null) {
            $sender->sendMessage($this->plugin->getMessage("home-not-found", ["{home}" => $homeName]));
            return false;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($homeData['world']);
        if ($world === null) {
            $sender->sendMessage($this->plugin->getMessage("world-not-found"));
            return false;
        }

        $position = new Position($homeData['x'], $homeData['y'], $homeData['z'], $world);
        $sender->teleport($position);
        $sender->sendMessage($this->plugin->getMessage("home-teleport", ["{home}" => $homeName]));
        
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
