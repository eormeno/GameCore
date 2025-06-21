<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use App\Models\GameApp;
use App\Enums\GameState;
use App\Models\GameUser;
use App\Enums\GameUserRole;
use App\Enums\GameUserStatus;
use App\Enums\GameUserJoinMethod;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\GameCancelledException;
use App\Exceptions\GameAlreadyStartedException;
use App\Exceptions\GameAlreadyFinishedException;
use App\Exceptions\MaxInstancesExceededException;

class GameInstanceService
{
    public function getOrCreateUserGame($user, GameApp $gameApp): Game
    {
        $count = $this->countUserGameInstances($user, $gameApp);
        if ($count < $gameApp->max_instances_per_user) {
            Game::factory()->forGameApp($gameApp)->forAuthUser()->withServices()->withGameObject()->create();
        }
        $currentGame = auth()->user()->games()->where('game_app_id', $gameApp->id)->first();
        $currentGame->gameObject;
        $currentGame->title = $gameApp->name;
        $currentGame->description = $gameApp->description;
        return $currentGame;
    }

    private function countUserGameInstances($user, GameApp $gameApp)
    {
        return $user->games()->where('game_app_id', $gameApp->id)->count();
    }

    public function getPlayerGameOptions(User $user, GameApp $gameApp): array
    {
        $activeGames = $gameApp->getUserActiveGames($user);
        $canCreateNew = $gameApp->canUserCreateNewInstance($user);

        return [
            'active_games' => $activeGames,
            'can_create_new' => $canCreateNew,
            'max_instances' => $gameApp->max_instances_per_user,
            'current_count' => $activeGames->count(),
            'game_type' => $gameApp->isSinglePlayer() ? 'single_player' : 'multiplayer'
        ];
    }

    public function validateUserCanJoinGame(User $user, GameApp $gameApp): void
    {
        if (!$gameApp->canUserCreateNewInstance($user)) {
            throw new MaxInstancesExceededException(
                "User has reached maximum instances ({$gameApp->max_instances_per_user}) for this game"
            );
        }
    }

    private function validateGameState(Game $game, GameApp $gameApp): void
    {
        if ($game->state === GameState::FINISHED) {
            throw new GameAlreadyFinishedException('Game has already finished');
        }

        if ($game->state === GameState::CANCELLED) {
            throw new GameCancelledException('Game has been cancelled');
        }

        if ($game->state === GameState::RUNNING && !$gameApp->allow_late_join) {
            throw new GameAlreadyStartedException('Cannot join running game when late join is disabled');
        }
    }

    private function findOpenGames(GameApp $gameApp): Collection
    {
        return Game::where('game_app_id', $gameApp->id)
            ->whereIn('state', [GameState::WAITING, GameState::RUNNING])
            ->with(['gameUsers' => function($query) {
                $query->where('status', GameUserStatus::ACTIVE)->with('user');
            }])
            ->get();
    }

    private function createNewGame(User $user, GameApp $gameApp): Game
    {
        $game = Game::create([
            'game_app_id' => $gameApp->id,
            'state' => GameState::WAITING,
            'invitation_code' => Str::random(8),
            'auto_authorize_players' => true, // o según configuración
            'name' => $gameApp->name . ' - ' . $user->name
        ]);

        GameUser::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'role' => GameUserRole::OWNER,
            'status' => GameUserStatus::ACTIVE,
            'join_method' => GameUserJoinMethod::AUTO,
            'joined_at' => now()
        ]);

        return $game;
    }

    private function buildWaitingResponse(Game $game, GameUser $currentUser, GameApp $gameApp): array
    {
        $users = $game->gameUsers()->with('user')->where('status', GameUserStatus::ACTIVE)->get();
        $currentPlayers = $users->count();
        $canStart = $currentUser->isOwner() && $currentPlayers >= $gameApp->min_players_per_instance;

        return [
            'waiting' => [
                'game' => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'invitationCode' => $game->invitation_code,
                    'state' => $game->state->name, // 'WAITING', 'RUNNING', etc.
                    'stateValue' => $game->state->value, // 0, 1, 2
                    'currentPlayers' => $currentPlayers,
                    'minPlayers' => $gameApp->min_players_per_instance,
                    'maxPlayers' => $gameApp->max_players_per_instance,
                    'users' => $users->map(function($gu) {
                        return [
                            'id' => $gu->user->id,
                            'name' => $gu->user->name,
                            'is_owner' => $gu->isOwner()
                        ];
                    }),
                    'createdAt' => $game->created_at
                ],
                'width' => $gameApp->width,
                'height' => $gameApp->height,
                'resourcesUrl' => $gameApp->resources_url,
                'showStartGameButton' => $canStart
            ]
        ];
    }

    private function buildOpenGamesResponse(Collection $games, GameApp $gameApp, User $user): array
    {
        $canCreateNew = $gameApp->canUserCreateNewInstance($user);

        return [
            'open_games' => [
                'games' => $games->map(function($game) {
                    return [
                        'id' => $game->id,
                        'name' => $game->name,
                        'invitationCode' => $game->invitation_code,
                        'state' => $game->state->name,
                        'stateValue' => $game->state->value,
                        'currentPlayers' => $game->gameUsers->where('status', GameUserStatus::ACTIVE)->count(),
                        'maxPlayers' => $game->gameApp->max_players_per_instance,
                        'users' => $game->gameUsers->where('status', GameUserStatus::ACTIVE)->map(function($gu) {
                            return [
                                'id' => $gu->user->id,
                                'name' => $gu->user->name,
                                'is_owner' => $gu->isOwner()
                            ];
                        }),
                        'createdAt' => $game->created_at
                    ];
                }),
                'width' => $gameApp->width,
                'height' => $gameApp->height,
                'resourcesUrl' => $gameApp->resources_url,
                'canCreateNew' => $canCreateNew
            ]
        ];
    }
}
