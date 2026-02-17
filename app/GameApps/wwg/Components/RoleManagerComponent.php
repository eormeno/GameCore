<?php

namespace App\GameApps\wwg\Components;

use App\Models\Components\PersistentComponent;
use App\Traits\HasNamespacePrefix;
use Auth;

class RoleManagerComponent extends PersistentComponent
{
    use HasNamespacePrefix;

    public static function config(): array
    {
        return [
            'role' => ['string', ''],
            'faction' => ['string', ''],
            'player_name' => ['string', ''],
            'player_id' => ['integer', 0],
        ];
    }
    public function onAwake(array $initParams): void
    {
        $userId = Auth::user()->id;
        $username = Auth::user()->name;

        $this->updateQuietly([
            'role' => $this->role, // This will be set when the player joins or creates a room
            'faction' => '',
            'player_name' => $username,
            'player_id' => $userId,
        ]);
    }

    public function onEnter(): void
    {
        if ($this->role === 'moderator') {
            $this->gameObject->findChild('moderator_view')->activate();
        } elseif ($this->role === 'werewolf') {
            $this->gameObject->findChild('night_action_view')->activate();
        } elseif ($this->role === 'villager') {
            $this->gameObject->findChild('night_idle_view')->activate();
        }
    }
    public function onExit(): void
    {
        $this->gameObject->findChild('night_action_view')->deactivate();
        $this->gameObject->findChild('night_idle_view')->deactivate();
    }

}