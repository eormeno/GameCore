<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;


class LobbyStateComponent extends PersistentComponent
{
    use HasNamespacePrefix;

    public function onEnter(): void
    {
        $lobbyView = $this->gameObject->findChild('lobby_view');
        if ($lobbyView) {
            $lobbyView->activate();
        }
    }

    public function onExit(): void
    {
        $lobbyView = $this->gameObject->findChild('lobby_view');
        if ($lobbyView) {
            $lobbyView->deactivate();
        }
    }

    public function onStartGameEvent(): string|null
    {
        return 'start_game';
    }

    public function onLeaveRoomEvent(): string|null
    {
        $this->clearRole();
        return 'initial_state';
    }

    public function clearRole(): void
    {
        $roleManager = $this->findGameObject(name: 'wwg.werewolves-root-prefab')->getComponent('role-manager');
        $roleManager->fill(attributes: ['role' => null]);
        return;
    }

}