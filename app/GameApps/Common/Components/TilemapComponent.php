<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilemapComponent extends PersistentComponent
{
    private const DEFAULT_MAP_NAME = "Map";
    private const MAP_WIDTH = 1024;
    private const MAP_HEIGHT = 1024;

    public static function config(): array
    {
        return [
            'width' => ['integer', self::MAP_WIDTH],
            'height' => ['integer', self::MAP_HEIGHT],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'width' => $initParams['width'] ?? self::MAP_WIDTH,
            'height' => $initParams['height'] ?? self::MAP_HEIGHT,
        ]);
    }

    public function view()
    {
        return [
            'type' => 'tilemap',
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
