<?php
namespace App\GameApps\wwg\prefabs;

use App\Models\Prefab\Prefab;

class VillagerRolePrefab extends Prefab
{
    public static function structure(): array
    {
        return [
            'villager_night_view:container' => self::villagerNightView(),
        ];
    }
    public static function villagerNightView(): array
    {
        return ['active' => false, 'attributes' => ['text' => 'You are a Villager. Try to survive the night!']];
    }
}