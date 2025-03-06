<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilesetComponent extends PersistentComponent
{
    private const TILE_WIDTH = 128;
    private const TILE_HEIGHT = 128;

	public static function config(): array
	{
		return [
			'tile_width' => ['integer', self::TILE_WIDTH],
            'tile_height' => ['integer', self::TILE_HEIGHT],
			'tilemap' => ['string', null],
		];
	}

	public function onAwake(array $initParams): void
	{
        $this->updateQuietly([
            'tile_width' => $initParams['tile_width'] ?? self::TILE_WIDTH,
            'tile_height' => $initParams['tile_height'] ?? self::TILE_HEIGHT,
            'tilemap' => $initParams['tilemap'] ?? null,
        ]);
    }

	public function view()
	{
        return view('tileset', [
            'tile_width' => $this->tile_width,
            'tile_height' => $this->tile_height,
            'tilemap' => $this->tilemap,
        ]);
    }
}
