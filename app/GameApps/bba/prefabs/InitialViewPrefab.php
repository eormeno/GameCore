<?php

namespace App\GameApps\bba\prefabs;

use App\Models\Prefab\Prefab;

class InitialViewPrefab extends Prefab
{
	public static function structure(): array
	{
		return [
			'background:container' => [
				'attributes' => ['layout' => 'vertical'],
				'title:label' => ['attributes' => ['text' => 'Bouncing Ball', 'style' => 'title']],
				'rules:label' => ['attributes' => ['text' => 'Click the ball to score points', 'style' => 'paragraph']],
				'scores:label' => ['attributes' => ['text' => 'Scores: 0', 'style' => 'paragraph']],
				'start_button:button' => ['attributes' => ['text' => 'Start', 'event' => 'start']],
			],
		];
	}
}
