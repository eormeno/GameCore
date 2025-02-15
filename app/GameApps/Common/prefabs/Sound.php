<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class Sound extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['sound' => []]
		];
	}
}
