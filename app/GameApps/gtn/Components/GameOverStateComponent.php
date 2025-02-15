<?php

namespace App\GameApps\gtn\Components;

use App\Traits\HasGTNPrefix;
use App\Models\Components\PersistentComponent;

class GameOverStateComponent extends PersistentComponent
{
	use HasGTNPrefix;

    protected $view_name = 'guess-the-number.game-over';

    public function onStart(): void
    {
		parent::onStart();
        $gtn_service = $this->getService('gtn-service');
        $params = [
            'user_name' => $gtn_service->user->name,
            'random_number' => $gtn_service->random_number,
        ];
        $this->updateView([
            'i18n' => [
                'notification' => $params,
                'subtitle' => $params,
                'play_again' => null,
                'exit' => null,
            ]
        ]);
    }

    public function onPlayAgainEvent()
    {
		return 'preparing';
    }

    public function onExitEvent()
    {
		return 'initial';
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
