<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class ButtonComponent extends PersistentComponent
{
    public static function config(): array
    {
        return [
            'event' => ['string', ''],
            'text' => ['string', ''],
            'style' => ['string', ''],
        ];
    }

	public function onAwake(array $initParams): void
	{
		$this->update($initParams);
	}

	public function view()
	{
		return [
			'parent' => $this->parentGameObject()->id ?? null,
			'type' => 'button',
			'event' => $this->event,
			'text' => $this->text,
			'style' => $this->style,
		];
	}
}
