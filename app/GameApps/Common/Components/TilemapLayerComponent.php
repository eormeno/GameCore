<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilemapLayerComponent extends PersistentComponent
{
    private const DEFAULT_LAYER_NAME = "Layer";
    private const LAYER_WIDTH = 1000;
    private const LAYER_HEIGHT = 1000;

    public static function config(): array
    {
        return [
            'order' => ['integer', 0],
            'name' => ['string', self::DEFAULT_LAYER_NAME],
            'x' => ['integer', 0],
            'y' => ['integer', 0],
            'width' => ['integer', self::LAYER_WIDTH],
            'height' => ['integer', self::LAYER_HEIGHT],
            'data' => ['json', null],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $width = $initParams['width'] ?? self::LAYER_WIDTH;
        $height = $initParams['height'] ?? self::LAYER_HEIGHT;
        $data = $this->buildLayerData($width, $height);
        $this->updateQuietly([
            'order' => $initParams['order'] ?? 0,
            'name' => $initParams['name'] ?? self::DEFAULT_LAYER_NAME,
            'x' => $initParams['x'] ?? 0,
            'y' => $initParams['y'] ?? 0,
            'width' => $width,
            'height' => $height,
            'data' => $data,
        ]);
    }

    private function buildLayerData(int $width, int $height): array
    {
        $data = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $row[] = '000';
            }
            $data[] = $row;
        }
        return $data;
    }

    public function view()
    {
        return [
            'type' => 'tilemap-layer',
            'order' => $this->order,
            'name' => $this->name,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'data' => $this->data,
        ];
    }
}
