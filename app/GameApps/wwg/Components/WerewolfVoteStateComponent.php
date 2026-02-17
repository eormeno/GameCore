<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class WerewolfVoteStateComponent extends PersistentComponent
{
	use HasNamespacePrefix;

	public function onEnter(): void
	{
		$werewolfVoteView = $this->gameObject->findChild('werewolf_vote_view');
		if ($werewolfVoteView) {
			$werewolfVoteView->activate();
		}
	}

	public function onExit(): void
	{
		$werewolfVoteView = $this->gameObject->findChild('werewolf_vote_view');
		if ($werewolfVoteView) {
			$this->log('WerewolfVoteStateComponent::onExit() found werewolf_vote_view');
			$werewolfVoteView->deactivate();
		}
	}
}
