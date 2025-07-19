<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\world\Position;
use zPluginsTeam\Warps\commands\HomeCommand;
use zPluginsTeam\Warps\commands\SetHomeCommand;
use zPluginsTeam\Warps\commands\DelHomeCommand;
use zPluginsTeam\Warps\commands\HomesCommand;
use zPluginsTeam\Warps\commands\WarpCommand;
use zPluginsTeam\Warps\commands\SetWarpCommand;
use zPluginsTeam\Warps\commands\DelWarpCommand;
use zPluginsTeam\Warps\commands\WarpsCommand;
use zPluginsTeam\Warps\commands\SetPortalCommand;
use zPluginsTeam\Warps\commands\DelPortalCommand;
use zPluginsTeam\Warps\commands\PortalsCommand;
use zPluginsTeam\Warps\utils\DataManager;
use zPluginsTeam\Warps\listeners\PortalListener;

class Main extends PluginBase implements Listener {

    private static Main $instance;
    private Config $config;
    private Config $messages;
    private DataManager $dataManager;
    private array $portalTexts = [];

    public function onEnable(): void {
        self::$instance = $this;
        
        // Save default resources
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        
        // Load configurations
        $this->config = $this->getConfig();
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        
        // Initialize data manager
        $this->dataManager = new DataManager($this);
        
        // Register commands
        $this->registerCommands();
        
        // Register event listener
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new PortalListener($this), $this);
        
    }

    private function registerCommands(): void {
        $commandMap = $this->getServer()->getCommandMap();
        
        $commandMap->register("warps", new HomeCommand($this));
        $commandMap->register("warps", new SetHomeCommand($this));
        $commandMap->register("warps", new DelHomeCommand($this));
        $commandMap->register("warps", new HomesCommand($this));
        $commandMap->register("warps", new WarpCommand($this));
        $commandMap->register("warps", new SetWarpCommand($this));
        $commandMap->register("warps", new DelWarpCommand($this));
        $commandMap->register("warps", new WarpsCommand($this));
        $commandMap->register("warps", new SetPortalCommand($this));
        $commandMap->register("warps", new DelPortalCommand($this));
        $commandMap->register("warps", new PortalsCommand($this));
    }

    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $to = $event->getTo();
        
        // Check for portal teleportation
        $portals = $this->dataManager->getPortals();
        foreach ($portals as $name => $portalData) {
            if (!isset($portalData['pos1']) || !isset($portalData['pos2']) || !isset($portalData['destination'])) {
                continue;
            }

            if ($to->getWorld()->getFolderName() !== $portalData['pos1']['world']) {
                continue;
            }

            // Check if player is within portal bounds
            $minX = min($portalData['pos1']['x'], $portalData['pos2']['x']);
            $maxX = max($portalData['pos1']['x'], $portalData['pos2']['x']);
            $minY = min($portalData['pos1']['y'], $portalData['pos2']['y']);
            $maxY = max($portalData['pos1']['y'], $portalData['pos2']['y']);
            $minZ = min($portalData['pos1']['z'], $portalData['pos2']['z']);
            $maxZ = max($portalData['pos1']['z'], $portalData['pos2']['z']);

            if ($to->getX() >= $minX && $to->getX() <= $maxX &&
                $to->getY() >= $minY && $to->getY() <= $maxY &&
                $to->getZ() >= $minZ && $to->getZ() <= $maxZ) {
                
                // Teleport player to destination
                $dest = $portalData['destination'];
                $destWorld = $this->getServer()->getWorldManager()->getWorldByName($dest['world']);
                if ($destWorld !== null) {
                    $player->teleport(new Position($dest['x'], $dest['y'], $dest['z'], $destWorld));
                    $player->sendMessage($this->getMessage("portal-teleport", ["{portal}" => $name]));
                }
                break;
            }
        }
    }

    public function getMessage(string $key, array $params = []): string {
        $prefix = $this->messages->get("prefix", "§6[Warps] §r");
        $message = $this->messages->get($key, "Message not found: " . $key);
        
        foreach ($params as $param => $value) {
            $message = str_replace($param, $value, $message);
        }
        
        return $prefix . $message;
    }

    public static function getInstance(): Main {
        return self::$instance;
    }

    public function getDataManager(): DataManager {
        return $this->dataManager;
    }

    public function getConfiguration(): Config {
        return $this->config;
    }

    public function getMessages(): Config {
        return $this->messages;
    }
}
