<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilemapComponent extends PersistentComponent
{
    private const DEFAULT_MAP_NAME = "Map";
    private const MAP_WIDTH = 1000;
    private const MAP_HEIGHT = 1000;

    public static function config(): array
    {
        return [
            'name' => ['string', self::DEFAULT_MAP_NAME],
            'width' => ['integer', self::MAP_WIDTH],
            'height' => ['integer', self::MAP_HEIGHT],
            'background' => ['string', null],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'name' => $initParams['name'] ?? self::DEFAULT_MAP_NAME,
            'width' => $initParams['width'] ?? self::MAP_WIDTH,
            'height' => $initParams['height'] ?? self::MAP_HEIGHT,
            'background' => $initParams['background'] ?? null,
        ]);
    }

    public function view()
    {
        return [
            'type' => 'tilemap',
            'name' => $this->name,
            'width' => $this->width,
            'height' => $this->height,
            'background' => $this->background,
        ];
    }
}
