<?php
namespace App\GameApps\Common\prefabs;

use App\Models\Prefab\Prefab;

class Label extends Prefab
{
	public static function structure(): array
	{
		return [
			'components' => ['label' => []]
		];
	}
}
