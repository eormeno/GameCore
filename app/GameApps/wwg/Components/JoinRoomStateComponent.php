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
			$joinRoomView->deactivate();
		}
	}
	public function onInputEvent(array $data): void
	{
		$this->updateView($data);
		$input_value = $data['value'] ?? '';
		$invitationCode = $this->getInvitationCode();

		if (strlen($input_value) < 13) {
			$warningTextGO = $this->gameObject->findChild('join_room_view')->findChild('warning_text');
			$warningTextGO->activate();
			$warningTextGO->getComponent('label')->updateQuietly(attributes: ['text' => 'The Room Code has to be 13 characters long']);
		} elseif (strlen($input_value) == 13) {
			$warningTextGO = $this->gameObject->findChild('join_room_view')->findChild('warning_text');
			$warningTextGO->deactivate();
		}
	}

	public function onJoinEvent(): string|null
	{
		$invitationCode = $this->getInvitationCode();
		$enteredRoomCode = $this->getTextInputRoomCode();

		if ($enteredRoomCode !== $invitationCode) {
			$warningTextGO = $this->gameObject->findChild('join_room_view')->findChild('warning_text');
			$warningTextGO->activate();
			$warningTextGO->getComponent('label')->updateQuietly(attributes: ['text' => 'Invalid Room Code']);
			return null;
		} else {
			return 'lobby';
		}

	}

	public function getInvitationCode(): string
	{
		$game = $this->game();
		return $game->where('id', $game->id)->value('invitation_code');
	}

	public function getTextInputRoomCode(): string
	{
		$roomCodeGO = $this->gameObject->findChild('join_room_view')->findChild('room_code_input');
		$roomInputComponent = $roomCodeGO->getComponent('text-input');
		return $roomInputComponent->getAttribute('value');
	}

	public function updateView(array $attributes): bool
	{
		$roomCodeGO = $this->gameObject->findChild('join_room_view')->findChild('room_code_input');
		if ($roomCodeGO) {
			$roomInputComponent = $roomCodeGO->getComponent('text-input');
			if ($roomInputComponent) {
				$roomInputComponent->updateQuietly(attributes: ['value' => $attributes['value'] ?? '']);
				return true;
			}
		}
		return false;
	}
}
