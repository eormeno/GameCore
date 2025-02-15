<?php

namespace App\GameApps\gtn\Components;

use App\Traits\HasGTNPrefix;
use App\Models\Components\PersistentComponent;

class GameDataComponent extends PersistentComponent
{
	use HasGTNPrefix;

	public static function config(): array
	{
		return [
			'score' => ['integer', 0],
			'max_attempts' => ['integer', 10],
			'min_number' => ['integer', 1],
			'max_number' => ['integer', 1024],
			'attempts' => ['integer', 0],
			'random_number' => ['integer', 0],
			'otron' => ['integer', 0],
		];
	}
}
