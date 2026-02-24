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
        //TODO: change the background image of the lobby view
    }

    public function onExit(): void
    {
        $lobbyView = $this->gameObject->findChild('lobby_view');
        if ($lobbyView) {
            $lobbyView->deactivate();
        }
    }


    public function onLeaveRoomEvent(): string|null
    {
        return 'initial';
    }


}