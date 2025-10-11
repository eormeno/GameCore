<?php

namespace App\Services;

use App\Models\GameApp;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GameAppService
{
    /**
     * Find an active GameApp by its ID
     *
     * @param int $id
     * @return GameApp
     * @throws ModelNotFoundException
     */
    public function findActiveById(int $id): GameApp
    {
        return GameApp::where('id', $id)
            ->where('active', true)
            ->firstOrFail();
    }

    public function getActiveGameAppsToApiFormat(): array
    {
        $activeGameApps = GameApp::where('active', true)->get();
        return $activeGameApps->map(fn($gameApp) => $this->gameAppToApiFormat($gameApp))->toArray();
    }

    /**
     * Transform GameApp model to API format with essential information
     *
     * @param GameApp $gameApp
     * @return array
     */
    public function gameAppToApiFormat(GameApp $gameApp): array
    {
        $gameAppInfo = $gameApp->only([
            'id',
            'name',
            'prefix',
            'width', 
            'height',
            'description', 
            'card_image', 
            'prefab_name',
            'max_instances_per_user', 
            'min_users_per_instance', 
            'max_users_per_instance', 
            'allow_late_join'
        ]);
        
        $gameAppInfo['resourcesUrl'] = route('res', $gameApp->id);
        
        return $gameAppInfo;
    }
}