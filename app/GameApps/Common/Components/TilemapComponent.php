<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilemapComponent extends PersistentComponent
{
    private const MAP_WIDTH = 1024;
    private const MAP_HEIGHT = 1024;

    public static function config(): array
    {
        return [
            'width' => ['integer', self::MAP_WIDTH],
            'height' => ['integer', self::MAP_HEIGHT],
            'view_port' => ['json', null],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'width' => $initParams['width'] ?? self::MAP_WIDTH,
            'height' => $initParams['height'] ?? self::MAP_HEIGHT,
            'view_port' => $initParams['view_port'] ?? null,
        ]);
    }

    public function view()
    {
        return [
            'type' => 'tilemap',
            'width' => $this->width,
            'height' => $this->height,
            'view_port' => $this->view_port,
        ];
    }
}
