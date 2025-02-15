<?php

namespace App\GameApps\bba\prefabs;

use App\Models\Prefab\Prefab;

class BouncingBallRoot extends Prefab
{

	public static function structure(): array
	{
		return [
			'states' => self::states(),
			'initial_view:container' => self::initialView(),
			'playing_view:container' => self::playingView(),
			'game_over_view:container' => self::gameOverView(),
		];
	}

	private static function initialView(): array
	{
		return [
			'active' => false,
			'attributes' => [
				'layout' => 'vertical',
				'image' => 'title-background.png',
				'width' => '100%',	// Expands horizontally to fill the parent container
				'height' => '100%'	// Expands vertically to fill the parent container
			],
			'title:label' => ['attributes' => ['text' => 'Bouncing Ball', 'style' => 'title']],
			'description:label' => ['attributes' => ['text' => 'Click the start button to begin', 'style' => 'paragraph']],
			'start_button:button' => ['attributes' => ['text' => 'Start', 'event' => 'start', 'style' => 'primary']],
			'title_sound:sound' => ['attributes' => ['sound' => 'title-music.wav', 'loop' => true, 'volume' => 0.25]],
		];
	}

	private static function playingView(): array
	{
		return [
			'active' => false,
			'attributes' => [
				'image' => 'playground.png',
				'width' => '100%',
				'height' => '100%'
			],
			'ball:sprite' => [
				'attributes' => [
					'texture' => 'soccer_ball.png',
					'width' => 32,
					'height' => 32,
					'scale' => 0.25,
					'x' => 400,
					'y' => 225
				]
			],
			// 'title:label' => ['attributes' => ['text' => 'Playing', 'style' => 'title']],
			'start_button:button' => ['attributes' => ['text' => 'Restart', 'event' => 'restart']],
		];
	}

	private static function gameOverView(): array
	{
		return [
			'active' => false,
			'attributes' => ['layout' => 'vertical'],
			'title:label' => ['attributes' => ['text' => 'Game Over', 'style' => 'title']],
			'start_button:button' => ['attributes' => ['text' => 'Restart', 'event' => 'restart']],
		];
	}

	private static function states(): array
	{
		return [
			'initial' => ['bba.initial-state' => []],
			'start' => ['bba.starting-state' => []],
			'playing' => ['bba.playing-state' => []],
			'game_over' => ['bba.game-over-state' => []],
		];
	}
}
