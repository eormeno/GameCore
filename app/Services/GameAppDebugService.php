<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameApp;
use App\Models\GameUser;
use App\Enums\GameState;
use App\Enums\GameUserStatus;

class GameAppDebugService
{

    /**
     * Get all installed game apps with basic information
     */
    public function getAllGameApps(bool $onlyActive = false): array
    {
        $query = GameApp::select('prefix', 'name', 'active');

        if ($onlyActive) {
            $query->where('active', true);
        }

        $gameApps = $query->get()
            ->map(function (GameApp $gameApp) {
                return [
                    'prefix' => $gameApp->prefix,
                    'name' => $gameApp->name,
                    'active' => $gameApp->active,
                ];
            })
            ->toArray();

        return $gameApps;
    }

    /**
     * Get detailed information for a specific game app
     */
    public function getGameAppDetails(string $prefix): ?array
    {
        $gameApp = GameApp::where('prefix', $prefix)->first();
        
        if (!$gameApp) {
            return null;
        }

        // Build detailed game app info from database
        $details = [
            'prefix' => $gameApp->prefix,
            'name' => $gameApp->name,
            'description' => $gameApp->description ?? '',
            'min_age' => $gameApp->min_age,
            'card_image' => $gameApp->image,
            'prefab_name' => $gameApp->prefab_name,
            'prefab_attributes' => $gameApp->prefab_attributes ?? [],
            'client' => $gameApp->client,
            'width' => $gameApp->width,
            'height' => $gameApp->height,
            'version' => $gameApp->version,
            'max_instances_per_user' => $gameApp->max_instances_per_user,
            'min_users_per_instance' => $gameApp->min_users_per_instance,
            'max_users_per_instance' => $gameApp->max_users_per_instance,
            'allow_late_join' => $gameApp->allow_late_join,
            'active' => $gameApp->active,
            'service_registry' => $gameApp->service_registry ?? [],
            'detailed_info' => $this->getDetailedInfo($gameApp)
        ];

        return $details;
    }

    /**
     * Get detailed information about instances and users for a game app
     */
    private function getDetailedInfo(?GameApp $gameApp): array
    {
        if (!$gameApp) {
            return [
                'total_instances' => 0,
                'active_instances' => 0,
                'total_users' => 0,
                'active_users' => 0,
                'instances' => []
            ];
        }

        $games = Game::where('game_app_id', $gameApp->id)
            ->with([
                'gameUsers' => function ($query) {
                    $query->with('user:id,name');
                }
            ])
            ->get();

        $activeGames = $games->whereIn('state', [GameState::WAITING, GameState::RUNNING]);
        
        $totalUsers = GameUser::whereIn('game_id', $games->pluck('id'))->distinct('user_id')->count();
        $activeUsers = GameUser::whereIn('game_id', $activeGames->pluck('id'))
            ->where('status', GameUserStatus::ACTIVE)
            ->distinct('user_id')
            ->count();

        $instances = $games->map(function (Game $game) {
            $gameUsers = $game->gameUsers->map(function (GameUser $gameUser) {
                return [
                    'id' => $gameUser->user->id,
                    'name' => $gameUser->user->name,
                    'is_owner' => $gameUser->role->value === 'owner',
                    'access_approved' => $gameUser->status === GameUserStatus::ACTIVE,
                    'invitation_approved' => $gameUser->status === GameUserStatus::ACTIVE
                ];
            });

            return [
                'id' => $game->id,
                'name' => $game->name,
                'state' => $game->state->value,
                'invitationCode' => $game->invitation_code,
                'createdAt' => $game->created_at->toISOString(),
                'users' => $gameUsers->toArray()
            ];
        });

        return [
            'total_instances' => $games->count(),
            'active_instances' => $activeGames->count(),
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'instances' => $instances->toArray()
        ];
    }
}