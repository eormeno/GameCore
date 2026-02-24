<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class CreateRoomStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$initialView = $this->gameObject->findChild('create_room_view');
		if ($initialView) {
			$initialView->activate();
		}
	}

	public function onExit(): void
	{
		$initialView = $this->gameObject->findChild('create_room_view');
		if ($initialView) {
			$initialView->deactivate();
		}
	}

	public function onCreateRoomCodeEvent(): string|null
	{
		$this->activateComponents();
		$this->showInvitationCode();
		return null;
	}

	private function showInvitationCode(): void
	{

		$game = $this->game();
		$invitationCode = $game->where('id', $game->id)->value('invitation_code');

		$roomCode = $invitationCode;
		$roomCodeLabelComponent = $this->gameObject->findChild('create_room_view')->findChild('room_code')->getComponent('label');
		$roomCodeLabelComponent->updateQuietly(attributes: ['text' => $roomCode]);

		return;
	}
	private function activateComponents(): void
	{
		$helpLabel = $this->gameObject->findChild('create_room_view')->findChild('help');
		if ($helpLabel) {
			$helpLabel->activate();
		}
		$roomCodeLabel = $this->gameObject->findChild('create_room_view')->findChild('room_code');
		if ($roomCodeLabel) {
			$roomCodeLabel->activate();
		}
		$lobbyButton = $this->gameObject->findChild('create_room_view')->findChild('lobby_button');
		if ($lobbyButton) {
			$lobbyButton->activate();
		}
		return;
	}

	public function onLobbyEvent(): string|null
	{
		return 'lobby';
	}
}
