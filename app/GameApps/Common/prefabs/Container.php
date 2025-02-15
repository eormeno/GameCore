<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class Container extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['container' => []]
		];
	}
}
