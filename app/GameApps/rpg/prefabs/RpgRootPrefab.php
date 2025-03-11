<?php

namespace App\GameApps\rpg\prefabs;

use App\Models\Prefab\Prefab;

class RpgRootPrefab extends Prefab
{

    public static function structure(): array
    {
        return [
            'land:tileset' => [
                'attributes' => ['number' => 0, 'image' => 'land.png'],
            ],
            'forest:tileset' => [
                'attributes' => ['number' => 1, 'image' => 'forest.png'],
            ],
            'decorations:tileset' => [
                'attributes' => ['number' => 2, 'image' => 'decorations.png'],
            ],
            'player:tileset' => [
                'attributes' => ['number' => 3, 'image' => 'player.png'],
            ],
            'npc:tileset' => [
                'attributes' => ['number' => 4, 'image' => 'npc.png'],
            ],
            'level_01:tilemap' => [
                'attributes' => ['width' => 1024, 'height' => 1024],
                'layer_00:tilemap-layer' => [
                    'attributes' => ['data' => 'land_layer.map'],
                ],
                'layer_01:tilemap-layer' => [
                    'attributes' => ['data' => 'forest_layer.map'],
                ],
                'layer_02:tilemap-layer' => [
                    'attributes' => ['data' => 'decorations_layer.map'],
                ],
                'collision:tilemap-layer' => [
                    'attributes' => ['data' => 'collision_layer.map'],
                ],
            ],
        ];
    }
}
