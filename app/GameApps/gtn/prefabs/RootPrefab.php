<?php

namespace App\GameApps\gtn\prefabs;

use App\Models\Prefab\Prefab;

class RootPrefab extends Prefab
{

	public static function structure(): array
	{
		return [
			'states' => self::states(),
			'components' => self::components(),
			'initial_view_2:container' => [
				'active' => false,
				'attributes' => [
					'apellido' => 'Ormeño'
				],
			],
			'showing_view' => [],
			'playing_view' => [],
			'success_view' => [],
			'gameover_view' => [],
		];
	}

	private static function states(): array
	{
		return [
			'initial' => ['gtn.initial-state-view' => []],
			'preparing' => ['gtn.preparing-state' => []],
			'showing_clue' => ['gtn.showing-clue-state' => []],
			'playing' => ['gtn.playing-state' => []],
			'success' => ['gtn.success-state' => []],
			'game_over' => ['gtn.game-over-state' => []],
		];
	}

	private static function components(): array
	{
		return [
			'gtn.game-data' => [],
		];
	}
}
