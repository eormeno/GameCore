<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class LabelComponent extends PersistentComponent
{
    public static function config(): array
    {
        return [
            'text' => ['string', ''],
            'style' => ['string', ''],
        ];
    }

	public function onAwake(array $initParams): void
	{
		$this->text = $initParams['text'] ?? '';
		$this->style = $initParams['style'] ?? '';
		$this->save();
	}

	public function view()
	{
		return [
			'parent' => $this->parentGameObject()->id ?? null,
			'type' => 'label',
			'text' => $this->text,
			'style' => $this->style,
		];
	}
}
