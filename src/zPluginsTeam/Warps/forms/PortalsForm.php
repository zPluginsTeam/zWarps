<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\form\FormValidationException;
use zPluginsTeam\Warps\Main;

class PortalsForm implements Form {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function jsonSerialize(): array {
        $portals = $this->plugin->getDataManager()->getPortals();
        $buttons = [];
        
        foreach ($portals as $name => $data) {
            $status = $data['enabled'] ? "§aEnabled" : "§cDisabled";
            $permissions = empty($data['permissions']) ? "None" : implode(", ", $data['permissions']);
            $buttons[] = [
                "text" => "§6{$name}\n{$status} | Perms: {$permissions}"
            ];
        }

        return [
            "type" => "form",
            "title" => "§6Portal Management",
            "content" => "Select a portal to manage:",
            "buttons" => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        $portals = array_keys($this->plugin->getDataManager()->getPortals());
        if (!isset($portals[$data])) {
            return;
        }

        $portalName = $portals[$data];
        $this->sendPortalEditForm($player, $portalName);
    }

    private function sendPortalEditForm(Player $player, string $portalName): void {
        $portalData = $this->plugin->getDataManager()->getPortal($portalName);
        if ($portalData === null) {
            return;
        }

        $player->sendForm(new class($this->plugin, $portalName) implements Form {
            private Main $plugin;
            private string $portalName;

            public function __construct(Main $plugin, string $portalName) {
                $this->plugin = $plugin;
                $this->portalName = $portalName;
            }

            public function jsonSerialize(): array {
                $portalData = $this->plugin->getDataManager()->getPortal($this->portalName);
                return [
                    "type" => "custom_form",
                    "title" => "§6Edit Portal: {$this->portalName}",
                    "content" => [
                        [
                            "type" => "toggle",
                            "text" => "Enabled",
                            "default" => $portalData['enabled']
                        ]
                  /**      [
                            "type" => "input",
                            "text" => "Required Permissions (comma separated)",
                            "placeholder" => "permission1, permission2",
                            "default" => implode(", ", $portalData['permissions'])
                        ] **/
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                if (!is_array($data) || count($data) !== 2) {
                    throw new FormValidationException("Invalid form data");
                }

                $portalData = $this->plugin->getDataManager()->getPortal($this->portalName);
                $portalData['enabled'] = (bool) $data[0];
                
                $permissions = array_filter(array_map('trim', explode(',', $data[1])));
                $portalData['permissions'] = $permissions;

                $this->plugin->getDataManager()->updatePortal($this->portalName, $portalData);
                $player->sendMessage($this->plugin->getMessage("portal-updated", ["{portal}" => $this->portalName]));
            }
        });
    }

    public function sendForm(Player $player): void {
        $player->sendForm($this);
    }
}
