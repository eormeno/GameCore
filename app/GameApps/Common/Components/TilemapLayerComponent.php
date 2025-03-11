<?php

namespace App\GameApps\Common\Components;

use App\Models\Components\PersistentComponent;

class TilemapLayerComponent extends PersistentComponent
{

    public static function config(): array
    {
        return [
            'order' => ['integer', 0],
            'data' => ['string', null],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateQuietly([
            'order' => $initParams['order'] ?? 0,
            'data' => $initParams['data'] ?? null,
        ]);
    }

    public function view()
    {
        return [
            'parent' => $this->parentGameObject()->id ?? null,
            'type' => 'tilemap-layer',
            'data' => $this->data,
        ];
    }
}
