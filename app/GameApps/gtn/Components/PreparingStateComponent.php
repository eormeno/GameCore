<?php

namespace App\GameApps\gtn\Components;

use App\Traits\HasGTNPrefix;
use App\Models\Components\PersistentComponent;

class PreparingStateComponent extends PersistentComponent
{
	use HasGTNPrefix;
	protected $view_name = 'guess-the-number.preparing';

	public function onStart(): void
	{
		parent::onStart();
		$this->getService('gtn-service')->startGame();
	}

	public function passTo(): string|null
	{
		return 'showing_clue';
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
