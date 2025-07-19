<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use zPluginsTeam\Warps\Main;

class PortalListener implements Listener {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();

        // Only trigger if player moved to a new block
        if ($event->getFrom()->floor() === $event->getTo()->floor()) {
            return;
        }

        $currentPos = $player->getPosition();

        foreach ($this->plugin->getDataManager()->getPortals() as $name => $portal) {
            if (!isset($portal["position1"], $portal["position2"])) continue;

            $pos1 = $this->plugin->getDataManager()->deserializePosition($portal["position1"]);
            $pos2 = $this->plugin->getDataManager()->deserializePosition($portal["position2"]);

            if ($this->isWithinArea($currentPos, $pos1)) {
                $player->teleport($pos2);
                $player->sendMessage("§aTeleported to portal §f'$name'");
                return;
            }

            if ($this->isWithinArea($currentPos, $pos2)) {
                $player->teleport($pos1);
                $player->sendMessage("§aTeleported to portal §f'$name'");
                return;
            }
        }
    }

    /**
     * Check if a position is within a 3x2x3 box centered around the target position,
     * including 1 block below the portal’s set Y.
     */
    private function isWithinArea(Vector3 $playerPos, Vector3 $portalPos): bool {
        return
            abs($playerPos->getFloorX() - $portalPos->getFloorX()) <= 1 &&
            abs($playerPos->getFloorY() - $portalPos->getFloorY()) <= 1 &&
            abs($playerPos->getFloorZ() - $portalPos->getFloorZ()) <= 1;
    }
}
