<?php

namespace App\GameApps\gtn\Components;

use App\Traits\HasGTNPrefix;
use App\Models\Components\PersistentComponent;

class ShowingClueStateComponent extends PersistentComponent
{
	use HasGTNPrefix;
	protected $view_name = 'guess-the-number.showing-clue';

	public function onStart(): void
	{
		parent::onStart();
		$clues = $this->getService('clue-service')->getClues();
		$messages = [
			'i18n' => [
				'title' => null,
				'good_luck' => null,
				'yes_button' => null,
				'another_challenge' => null,
				'clues' => $clues
			]
		];
		$this->updateView($messages);
	}

	public function onWantToPlayEvent()
	{
		return 'playing';
	}

	public function onAnotherChallengeEvent()
	{
		return 'preparing';
	}

	public function view()
	{
		$data = $this->messages();
		if (!isset($this->view_name)) {
			$this->view_name = 'default';
		}
		return base64_encode(view($this->view_name, $data));
	}
}
