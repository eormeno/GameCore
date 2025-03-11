<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class Camera2D extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['camera-2d' => []]
		];
	}
}
