<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class TilemapLayer extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['tilemap-layer' => []]
		];
	}
}
