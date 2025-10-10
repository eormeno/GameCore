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

    /**
     * Get all active GameApps with specified fields
     *
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActive(array $fields = ['*'])
    {
        return GameApp::where('active', true)->get($fields);
    }

    /**
     * Transform GameApp model to API format with essential information
     *
     * @param GameApp $gameApp
     * @return array
     */
    public function toApiFormat(GameApp $gameApp): array
    {
        $gameAppInfo = $gameApp->only([
            'name',
            'width', 
            'height', 
            'max_instances_per_user', 
            'min_users_per_instance', 
            'max_users_per_instance', 
            'allow_late_join'
        ]);
        
        $gameAppInfo['resourcesUrl'] = route('res', $gameApp->id);
        
        return $gameAppInfo;
    }

    // /**
    //  * Transform GameApp model to detailed API format for individual game responses
    //  *
    //  * @param GameApp $gameApp
    //  * @return array
    //  */
    // public function toDetailedApiFormat(GameApp $gameApp): array
    // {
    //     return [
    //         'resourcesUrl' => route('res', $gameApp->id),
    //         'width' => $gameApp->width,
    //         'height' => $gameApp->height,
    //         'maxInstancesPerUser' => $gameApp->max_instances_per_user,
    //         'minUsersPerInstance' => $gameApp->min_users_per_instance,
    //         'maxUsersPerInstance' => $gameApp->max_users_per_instance,
    //     ];
    // }
}