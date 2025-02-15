<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;


class SoundComponent extends PersistentComponent
{
	public static function config(): array
	{
		return [
			'sound' => ['string', null],
			'loop' => ['boolean', false],
			'volume' => ['float', 1.0],
			'autoplay' => ['boolean', true],
		];
	}

	public function onAwake(array $initParams): void
	{
		$this->sound = $initParams['sound'] ?? null;
		$this->loop = $initParams['loop'] ?? false;
		$this->volume = $initParams['volume'] ?? 1.0;
		$this->autoplay = $initParams['autoplay'] ?? true;
		$this->save();
	}

	public function view()
	{
		return [
			'parent' => $this->parentGameObject()->id ?? null,
			'type' => 'sound',
			'sound' => $this->sound,
			'loop' => $this->loop,
			'volume' => $this->volume,
			'autoplay' => $this->autoplay,
		];
	}
}
