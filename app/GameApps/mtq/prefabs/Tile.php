<?php

namespace App\GameApps\mtq\prefabs;

use App\Models\Prefab\Prefab;

class Tile extends Prefab
{

	public static function structure(): array
	{
		return [
			'components' => ['mtq.tile' => []]
		];
	}
}
