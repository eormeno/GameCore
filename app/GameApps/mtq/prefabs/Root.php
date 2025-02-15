<?php

namespace App\GameApps\mtq\prefabs;

use App\Models\Prefab\Prefab;

class Root extends Prefab
{

	public static function structure(): array
	{
		return [
			'states' => self::states(),
			'initial_view:container' => self::initialView(),
			'menu_view:container' => self::menuView(),
			'playing_view:container' => self::playingView(),
		];
	}

	private static function initialView()
	{
		return [
			'active' => true,
			'attributes' => [
				'layout' => 'vertical',
				'image' => 'initial_background.png',
				'width' => '100%',
				'height' => '100%'
			],
			'title:label' => ['attributes' => ['text' => 'Mythic Treasure Quest', 'style' => 'title']],
			'start_button:button' => ['attributes' => ['text' => 'Start', 'event' => 'start', 'style' => 'primary']],
		];
	}

	private static function menuView(): array
	{
		return [
			'active' => false,
			'attributes' => [
				'layout' => 'vertical',
				'image' => 'menu_background.png',
				'width' => '100%',
				'height' => '100%'
			],
			'title:label' => ['attributes' => ['text' => 'Mythic Treasure Quest', 'style' => 'title']],
			'description:label' => ['attributes' => ['text' => 'Un juego en donde exploras templos, palacios y criptas antiguas y encuentras tesoros y posiones usando las mecánicas del clásico juego buscaminas. ¡Pero ten cuidado! También hay trampas, monstruos y maldiciones.', 'style' => 'paragraph']],
			'accept_text:label' => ['attributes' => ['text' => 'Si aceptas el desafío, presiona el botón para comenzar', 'style' => 'paragraph']],
			'start_button:button' => ['attributes' => ['text' => 'Start', 'event' => 'start', 'style' => 'primary']],
			'title_sound:sound' => ['attributes' => ['sound' => 'title-music.wav', 'loop' => true, 'volume' => 0.25]],
		];
	}

	private static function playingView(): array
	{
		return [
			'active' => false,
			'attributes' => [
				'layout' => 'vertical',
				'image' => 'playing_background.png',
				'width' => '100%',
				'height' => '100%'
			],
			// 'back_button:button' => ['attributes' => ['text' => 'Back', 'event' => 'start', 'style' => 'primary']],
			'tileset:container' => self::tileMatrix(8, 8),
		];
	}

	private static function tileMatrix(int $rows, int $cols): array
	{
		$size = 64;
		$hgap = 0;
		$vgap = 0;
		$matrix = [];
		$matrix['active'] = true;
		$matrix['attributes'] = ['x' => 30, 'y' => 80, 'image' => 'tileset_background.png', 'width' => '540px', 'height' => '540px'];
		for ($i = 0; $i < $rows; $i++) {
			for ($j = 0; $j < $cols; $j++) {
				$x = $i * $size + $i * $hgap;
				$y = $j * $size + $j * $vgap;
				$matrix["tile{$i}{$j}:mtq.tile"] = ['attributes' => ['x' => $x, 'y' => $y]];
			}
		}
		$hsize = $cols * $size + ($cols - 1) * $hgap;
		$vsize = $rows * $size + ($rows - 1) * $vgap;
		$matrix['attributes']['x'] = (600 - $hsize) / 2;
		$matrix['attributes']['y'] = (700 - $vsize) / 2;
		$matrix['attributes']['width'] = "{$hsize}px";
		$matrix['attributes']['height'] = "{$vsize}px";
		return $matrix;
	}

	private static function states(): array
	{
		return [
			'initial' => ['mtq.initial-state' => []],
			'menu' => ['mtq.menu-state' => []],
			'playing' => ['mtq.playing-state' => []],
		];
	}
}
