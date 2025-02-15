<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class SpriteComponent extends PersistentComponent
{
    public static function config(): array
    {
        return [
			'texture' => ['string', ''],
			'layer' => ['integer', 0],
            'x' => ['integer', 0],
            'y' => ['integer', 0],
			'width' => ['integer', 0],
			'height' => ['integer', 0],
            'scale' => ['float', 1.0],
			'pivot_x' => ['float', 0.5],
			'pivot_y' => ['float', 0.5],
			'rotation' => ['float', 0],
			'visible' => ['boolean', true],
        ];
    }

	public function onAwake(array $initParams): void
	{
		$this->texture = $initParams['texture'] ?? '';
		$this->layer = $initParams['layer'] ?? 0;
		$this->x = $initParams['x'] ?? 0;
		$this->y = $initParams['y'] ?? 0;
		$this->scale = $initParams['scale'] ?? 1.0;
		$this->pivot_x = $initParams['pivot_x'] ?? 0.5;
		$this->pivot_y = $initParams['pivot_y'] ?? 0.5;
		$this->rotation = $initParams['rotation'] ?? 0;
		$this->visible = $initParams['visible'] ?? true;
		$this->save();
	}

	public function view()
	{
		return [
			'parent' => $this->parentGameObject()->id ?? null,
			'type' => 'sprite',
			'texture' => $this->texture,
			'layer' => $this->layer,
			'x' => $this->x,
			'y' => $this->y,
			'scale' => $this->scale,
			'pivot_x' => $this->pivot_x,
			'pivot_y' => $this->pivot_y,
			'rotation' => $this->rotation,
			'visible' => $this->visible,
		];
	}
}
