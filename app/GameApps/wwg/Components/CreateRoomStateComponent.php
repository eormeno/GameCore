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
		$invitationCode =$game->where('id', $game->id)->value('invitation_code');
		
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
		//update CreateRoomView
		return;
	}

	public function onLobbyEvent(): string|null
	{
		return 'lobby';
	}



	// public function roleBasedRender()	
	// {
	// 	$roleManager = $this->findGameObject(name: 'wwg.werewolves-root-prefab')->getComponent('role-manager');
	// 	if ($roleManager->role === 'moderator') {
	// 		return 'moderator_view';
	// 	} elseif ($roleManager->role === 'werewolf') {
	// 		return 'night_action_view';
	// 	} elseif ($roleManager->role === 'villager') {
	// 		return 'night_idle_view';
	// 	}
	// 	return null;
	// }

	// public function activateViewBasedOnRole( string $roleName): void
	// {
	// 	$viewName = $this->roleBasedRender();
	// 	if ($viewName) {
	// 		$view = $this->gameObject->findChild($viewName);
	// 		if ($view) {
	// 			$view->activate();
	// 		}
	// 	}
	// }




}
