<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\utils;

use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\player\Player;
use zPluginsTeam\Warps\Main;

class DataManager {

    private Main $plugin;
    private Config $homes;
    private Config $warps;
    private Config $portals;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        
        // Initialize data files
        $this->homes = new Config($plugin->getDataFolder() . "homes.yml", Config::YAML);
        $this->warps = new Config($plugin->getDataFolder() . "warps.yml", Config::YAML);
        $this->portals = new Config($plugin->getDataFolder() . "portals.yml", Config::YAML);
    }

    public function setHome(Player $player, string $name): bool {
        $playerName = strtolower($player->getName());
        $maxHomes = $this->plugin->getConfiguration()->get("max-homes", 5);
        
        $playerHomes = $this->homes->get($playerName, []);
        
        if (count($playerHomes) >= $maxHomes && !isset($playerHomes[$name])) {
            return false;
        }

        $position = $player->getPosition();
        $playerHomes[$name] = [
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "world" => $position->getWorld()->getFolderName()
        ];

        $this->homes->set($playerName, $playerHomes);
        $this->homes->save();
        return true;
    }

    public function getHome(Player $player, string $name): ?array {
        $playerName = strtolower($player->getName());
        $playerHomes = $this->homes->get($playerName, []);
        
        return $playerHomes[$name] ?? null;
    }

    public function deleteHome(Player $player, string $name): bool {
        $playerName = strtolower($player->getName());
        $playerHomes = $this->homes->get($playerName, []);
        
        if (!isset($playerHomes[$name])) {
            return false;
        }

        unset($playerHomes[$name]);
        $this->homes->set($playerName, $playerHomes);
        $this->homes->save();
        return true;
    }

    public function getPlayerHomes(Player $player): array {
        $playerName = strtolower($player->getName());
        return $this->homes->get($playerName, []);
    }

    public function setWarp(string $name, Position $position, array $permissions = []): void {
        $warpData = [
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "world" => $position->getWorld()->getFolderName(),
            "permissions" => $permissions,
            "enabled" => true
        ];

        $this->warps->set($name, $warpData);
        $this->warps->save();
    }


    public function deleteWarp(string $name): bool {
        if (!$this->warps->exists($name)) {
            return false;
        }

        $this->warps->remove($name);
        $this->warps->save();
        return true;
    }

    public function getWarp(string $name): ?array {
        $data = $this->warps->get($name);
        return is_array($data) ? $data : null;
    }

    public function updateWarp(string $name, array $data): void {
        $this->warps->set($name, $data);
        $this->warps->save();
    }
    
    public function getWarps(): array {
        return $this->warps->getAll();
    }

    public function setPortalPosition(string $name, Position $position, int $step): bool {
        $portalData = $this->portals->get($name, []);
        
        $posKey = "pos" . $step;
        $portalData[$posKey] = [
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ(),
            "world" => $position->getWorld()->getFolderName()
        ];

        $this->portals->set($name, $portalData);
        $this->portals->save();
        
        return isset($portalData['pos1']) && isset($portalData['pos2']);
    }

    public function setPortalDestination(string $name, Position $destination): void {
        $portalData = $this->portals->get($name, []);
        $portalData['destination'] = [
            "x" => $destination->getX(),
            "y" => $destination->getY(),
            "z" => $destination->getZ(),
            "world" => $destination->getWorld()->getFolderName()
        ];
        $portalData['enabled'] = true;
        $portalData['permissions'] = [];

        $this->portals->set($name, $portalData);
        $this->portals->save();
    }

    public function getPortal(string $name): ?array {
        return $this->portals->get($name);
    }

    public function deletePortal(string $name): bool {
        if (!$this->portals->exists($name)) {
            return false;
        }

        $this->portals->remove($name);
        $this->portals->save();
        return true;
    }

    public function getPortals(): array {
        return $this->portals->getAll();
    }

    public function updatePortal(string $name, array $data): void {
        $this->portals->set($name, $data);
        $this->portals->save();
    }
}
