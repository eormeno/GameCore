<?php

namespace App\Services\Screens;

use App\Models\User;
use App\Models\GameApp;
use App\Services\GameAppService;
use App\Services\GameInstanceService;

class GameLobbyScreenService
{
    private GameAppService $gameAppService;
    private GameInstanceService $gameInstanceService;

    public function __construct(
        GameAppService $gameAppService,
        GameInstanceService $gameInstanceService
    ) {
        $this->gameAppService = $gameAppService;
        $this->gameInstanceService = $gameInstanceService;
    }

    /**
     * Get the game lobby screen data for a user and game app
     *
     * @param User $user
     * @param GameApp $gameApp
     * @return array
     */
    public function getGameLobbyScreen(User $user, GameApp $gameApp): array
    {
        $savedGames = $this->gameInstanceService->getSavedGames($user, $gameApp);
        $saved_games = $this->gameInstanceService->toApiFormat($savedGames);
        $canCreateNewGame = $this->canCreateNewGame($user, $gameApp);
        $maxInstances = $gameApp->max_instances_per_user;

        $ui = $this->uiElements($canCreateNewGame, $maxInstances, $saved_games);

        return [
            'game_lobby_screen:container' => [
                'layout' => 'vertical',
                'title' => t('game_lobby_title', ['game_app' => $gameApp->name]),
                'elements' => $ui,
            ]
        ];
    }

    /**
     * Define UI elements for the game lobby screen
     * 
     * @return array
     */
    private function uiElements(bool $canCreateNewGame, int $maxInstances, array $saved_games): array
    {
        $newGameButtonTooltip = $canCreateNewGame ? t('new_game_button_tooltip') : t('cannot_create_new_game');
        $warningMessage = $canCreateNewGame ? null : t('instances_limit_reached', ['max' => $maxInstances]);

        return [
            'new_game:button' => [
                'label' => t('new_game_button_label'),
                'tooltip' => $newGameButtonTooltip,
                'action' => 'create_new_game',
                'enabled' => $canCreateNewGame,
                'icon' => 'plus',
                'style' => 'primary',
            ],

            'warning_message:label' => ['text' => $warningMessage, 'visible' => !is_null($warningMessage)],
            'saved_games:table' => $this->savedGamesTable($saved_games, $maxInstances),
        ];
    }

    private function savedGamesTable(array $saved_games, int $maxInstances): array
    {
        $headers = [
            ['header' => t('saved_game_number_column')],
            ['header' => t('saved_game_name_column')],
            ['header' => t('saved_game_role_column')],
            ['header' => t('saved_game_status_column')],
            ['header' => t('saved_game_last_played_column')],
            ['header' => t('saved_game_actions_column')],
        ];
        $title = count($saved_games) > 0 ?
            t('saved_games_title') :
            t('no_saved_games_title');
        $rows = [];
        $gameNumber = 1;
        foreach ($saved_games as $game) {
            $savedGameId = $game['id'] ?? null;
            $rows[] = [
                $gameNumber++,
                $game['name'] ?? t('default_game_name'),
                $game['state'],
                $game['game_user']['role'],
                $game['game_user']['status'],
                $game['game_user']['join_method'],
                $game['game_user']['last_played_at_human'],
                "saved_game_{$savedGameId}_actions:container" => [
                    'layout' => 'horizontal',
                    'elements' => [
                        $this->playGameButton($savedGameId),
                        $this->deleteGameButton($savedGameId),
                    ],
                ],
            ];
        }

        // Fill empty rows until we reach max instances per user
        $emptyRowsCount = $maxInstances - count($saved_games);
        for ($i = 0; $i < $emptyRowsCount; $i++) {
            $rows[] = [
                $gameNumber++,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'actions' => [],
            ];
        }

        return [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function playGameButton(int $gameId): array
    {
        return [
            "play_{$gameId}_game:button" => [
                'label' => t('play_game_action'),
                'action' => 'play_game',
                'parameters' => ['game_id' => $gameId],
                'icon' => 'play',
                'style' => 'success',
            ],
        ];
    }

    private function deleteGameButton(int $gameId): array
    {
        return [
            "delete_{$gameId}_game:button" => [
                'label' => t('delete_game_action'),
                'action' => 'delete_game',
                'parameters' => ['game_id' => $gameId],
                'icon' => 'trash',
                'style' => 'danger',
            ],
        ];
    }

    private function canCreateNewGame(User $user, GameApp $gameApp): bool
    {
        $savedGamesCount = $this->gameInstanceService->countActiveUserGameInstances($user, $gameApp);

        if ($gameApp->max_instances_per_user > 0) {
            return $savedGamesCount < $gameApp->max_instances_per_user;
        }

        return true; // No limit
    }

    /**
     * Get the expected response structure (useful for validation/testing)
     * 
     * @return array
     */
    public static function getResponseStructure(): array
    {
        return GameLobbyScreenStructure::response();
    }
}
