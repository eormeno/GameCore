<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilesetComponent extends PersistentComponent
{
    private const DEFAULT_NUMBER = 0;
    private const TILE_WIDTH = 128;
    private const TILE_HEIGHT = 128;

    public static function config(): array
    {
        return [
            'number' => ['integer', self::DEFAULT_NUMBER],
            'tile_width' => ['integer', self::TILE_WIDTH],
            'tile_height' => ['integer', self::TILE_HEIGHT],
            'image' => ['string', null],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'number' => $initParams['number'] ?? self::DEFAULT_NUMBER,
            'tile_width' => $initParams['tile_width'] ?? self::TILE_WIDTH,
            'tile_height' => $initParams['tile_height'] ?? self::TILE_HEIGHT,
            'image' => $initParams['image'] ?? null,
        ]);
    }

    public function view()
    {
        return [
            'type' => 'tileset',
            'number' => $this->number,
            'tile_width' => $this->tile_width,
            'tile_height' => $this->tile_height,
            'image' => $this->image,
        ];
    }
}
