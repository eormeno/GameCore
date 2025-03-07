<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class Tilemap extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['tilemap' => []]
		];
	}
}
