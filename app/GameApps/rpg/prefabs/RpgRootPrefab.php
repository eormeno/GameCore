<?php

namespace App\GameApps\rpg\prefabs;

use App\Models\Prefab\Prefab;

class RpgRootPrefab extends Prefab
{

    public static function structure(): array
    {
        return [
            'land:tileset' => [
                'attributes' => ['number' => 0, 'tile_width' => 128, 'tile_height' => 128, 'image' => 'land.png'],
            ],
            'water:tileset' => [
                'attributes' => ['number' => 1,'tile_width' => 128, 'tile_height' => 128, 'image' => 'water.png'],
            ],
            'building:tileset' => [
                'attributes' => ['number' => 2,'tile_width' => 128, 'tile_height' => 128, 'image' => 'building.png'],
            ],
            'main:container' => [
                'attributes' => ['layout' => 'horizontal', 'width' => '100%', 'height' => '100%'],
                'dec_button:button' => ['attributes' => ['text' => '-', 'event' => 'decrement', 'style' => 'primary']],
                'number:label' => ['attributes' => ['text' => '0', 'style' => 'label-center']],
                'inc_button:button' => ['attributes' => ['text' => '+', 'event' => 'increment', 'style' => 'primary']],
            ],
            'components' => [
                'cnt.counter' => ['min' => 0, 'max' => 100, 'step' => 5, 'value' => 50],
            ],
        ];
    }
}
