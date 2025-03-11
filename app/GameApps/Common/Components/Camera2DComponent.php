<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class Camera2DComponent extends PersistentComponent
{
    private const WIDTH = 320;
    private const HEIGHT = 180;

    public static function config(): array
    {
        return [
            'x' => ['integer', 160],
            'y' => ['integer', 90],
            'width' => ['integer', self::WIDTH],
            'height' => ['integer', self::HEIGHT],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'x' => $initParams['x'] ?? 0,
            'y' => $initParams['y'] ?? 0,
            'width' => $initParams['width'] ?? self::WIDTH,
            'height' => $initParams['height'] ?? self::HEIGHT,
        ]);
    }

    public function view()
    {
        return [
            'type' => 'camara-2d',
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
