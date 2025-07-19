<?php

declare(strict_types=1);

namespace zPluginsTeam\Warps\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\form\FormValidationException;
use zPluginsTeam\Warps\Main;

class WarpsForm implements Form {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function jsonSerialize(): array {
        $warps = $this->plugin->getDataManager()->getWarps();
        $buttons = [];
        
        foreach ($warps as $name => $data) {
            $status = $data['enabled'] ? "§aEnabled" : "§cDisabled";
            $permissions = empty($data['permissions']) ? "None" : implode(", ", $data['permissions']);
            $buttons[] = [
                "text" => "§6{$name}\n{$status} | Perms: {$permissions}"
            ];
        }

        return [
            "type" => "form",
            "title" => "§6Warp Management",
            "content" => "Select a warp to manage:",
            "buttons" => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        $warps = array_keys($this->plugin->getDataManager()->getWarps());
        if (!isset($warps[$data])) {
            return;
        }

        $warpName = $warps[$data];
        $this->sendWarpEditForm($player, $warpName);
    }

    private function sendWarpEditForm(Player $player, string $warpName): void {
        $warpData = $this->plugin->getDataManager()->getWarp($warpName);
        if ($warpData === null) {
            return;
        }

        $form = [
            "type" => "custom_form",
            "title" => "§6Edit Warp: {$warpName}",
            "content" => [
                [
                    "type" => "toggle",
                    "text" => "Enabled",
                    "default" => $warpData['enabled']
                ],
                [
                    "type" => "input",
                    "text" => "Required Permissions (comma separated)",
                    "placeholder" => "permission1, permission2",
                    "default" => implode(", ", $warpData['permissions'])
                ]
            ]
        ];

        $player->sendForm(new class($this->plugin, $warpName) implements Form {
            private Main $plugin;
            private string $warpName;

            public function __construct(Main $plugin, string $warpName) {
                $this->plugin = $plugin;
                $this->warpName = $warpName;
            }

            public function jsonSerialize(): array {
                $warpData = $this->plugin->getDataManager()->getWarp($this->warpName);
                return [
                    "type" => "custom_form",
                    "title" => "§6Edit Warp: {$this->warpName}",
                    "content" => [
                        [
                            "type" => "toggle",
                            "text" => "Enabled",
                            "default" => $warpData['enabled']
                        ],
                        [
                            "type" => "input",
                            "text" => "Required Permissions (comma separated)",
                            "placeholder" => "permission1, permission2",
                            "default" => implode(", ", $warpData['permissions'])
                        ]
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

                $warpData = $this->plugin->getDataManager()->getWarp($this->warpName);
                $warpData['enabled'] = (bool) $data[0];
                
                $permissions = array_filter(array_map('trim', explode(',', $data[1])));
                $warpData['permissions'] = $permissions;

                $this->plugin->getDataManager()->updateWarp($this->warpName, $warpData);
                $player->sendMessage($this->plugin->getMessage("warp-updated", ["{warp}" => $this->warpName]));
            }
        });
    }

    public function sendForm(Player $player): void {
        $player->sendForm($this);
    }
}
