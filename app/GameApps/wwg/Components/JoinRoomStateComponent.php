<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class JoinRoomStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$joinRoomView = $this->gameObject->findChild('join_room_view');
		if ($joinRoomView) {
			$joinRoomView->activate();
		}
	}

	public function onExit(): void
	{
		$joinRoomView = $this->gameObject->findChild('join_room_view');
		if ($joinRoomView) {
			$this->log('JoinRoomStateComponent::onExit() found join_room_view');
			$joinRoomView->deactivate();
		}
	}
}
