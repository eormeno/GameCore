<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;


class ContainerComponent extends PersistentComponent
{
	public static function config(): array
	{
		return [
			'x' => ['integer', null],
			'y' => ['integer', null],
			'layout' => ['string', 'vertical'],
			'width' => ['string','100%'],
			'height' => ['string', '100%'],
			'image' => ['string', null],
		];
	}

	public function onAwake(array $initParams): void
	{
		$this->x = $initParams['x'] ?? null;
		$this->y = $initParams['y'] ?? null;
		$this->layout = $initParams['layout'] ?? 'vertical';
		$this->width = $initParams['width'] ?? '100%';
		$this->height = $initParams['height'] ?? '100%';
		$this->image = $initParams['image'] ?? null;
		$this->save();
	}

	public function view()
	{
		$ret = [
			'parent' => $this->parentGameObject()->id ?? null,
			'type' => 'container',
			'layout' => $this->layout,
			'width' => $this->width,
			'height' => $this->height,
			'image' => $this->image,
		];
		if ($this->x !== null) {
			$ret['x'] = $this->x;
		}
		if ($this->y !== null) {
			$ret['y'] = $this->y;
		}
		return $ret;
	}
}
