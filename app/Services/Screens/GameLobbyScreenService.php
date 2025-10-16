<?php

namespace App\Services\Screens;

use App\Models\User;
use App\Models\GameApp;
use App\Services\GameInstanceService;
use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;

class GameLobbyScreenService
{
    private GameInstanceService $gameInstanceService;

    public function __construct(
        GameInstanceService $gameInstanceService
    ) {
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
        $canCreateNewGame = $this->gameInstanceService->canCreateNewGame($user, $gameApp);
        $maxInstances = $gameApp->max_instances_per_user;

        $ui = $this->buildUIElements($canCreateNewGame, $maxInstances, $saved_games);

        return UIBuilder::container('game_lobby_screen')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title(t('games.game_lobby_title', ['name' => $gameApp->name]))
            ->elements($ui)
            ->build();
    }

    /**
     * Define UI elements for the game lobby screen
     * 
     * @return array
     */
    private function buildUIElements(bool $canCreateNewGame, int $maxInstances, array $saved_games): array
    {
        $elements = [];

        // New Game Button
        $elements += UIBuilder::button('new_game')
            ->label(t('new_game_button_label'))
            ->action('create_new_game')
            ->icon('plus')
            ->style('primary')
            ->enabled($canCreateNewGame)
            ->tooltip($canCreateNewGame ? t('new_game_button_tooltip') : t('cannot_create_new_game'))
            ->build();

        // Warning Message (only if needed)
        $elements += UIBuilder::label('warning_message')
            ->text(t('instances_limit_reached', ['max' => $maxInstances]))
            ->style('warning')
            ->visible(!$canCreateNewGame)
            ->build();

        // Saved Games Table
        $elements += $this->buildSavedGamesTable($saved_games, $maxInstances);

        return $elements;
    }

    private function buildSavedGamesTable(array $saved_games, int $maxInstances): array
    {
        $rows = $this->buildTableRows($saved_games, $maxInstances);

        return UIBuilder::table('saved_games')
            ->title(count($saved_games) > 0 ? t('games.saved_games_title') : t('no_saved_games_title'))
            ->addHeader(t('games.saved_game_number_column'))
            ->addHeader(t('games.saved_game_name_column'))
            ->addHeader(t('games.saved_game_state_column'))
            ->addHeader(t('games.saved_game_role_column'))
            ->addHeader(t('games.saved_game_status_column'))
            ->addHeader(t('games.saved_game_join_method_column'))
            ->addHeader(t('games.saved_game_last_played_column'))
            ->addHeader(t('games.saved_game_actions_column'), 'game_actions', width: '200px')
            ->rows($rows)
            ->build();
    }

    private function buildTableRows(array $saved_games, int $maxInstances): array
    {
        $rows = [];
        $gameNumber = 1;

        // Rows with saved games
        foreach ($saved_games as $game) {
            $rows[] = $this->buildGameRow($game, $gameNumber++);
        }

        // Empty rows
        $emptyRowsCount = $maxInstances - count($saved_games);
        for ($i = 0; $i < $emptyRowsCount; $i++) {
            $rows[] = $this->buildEmptyRow($gameNumber++);
        }

        return $rows;
    }

    private function buildGameRow(array $game, int $gameNumber): array
    {
        $savedGameId = $game['id'] ?? null;

        $actionsContainer = UIBuilder::container("saved_game_{$savedGameId}_actions")
            ->layout(LayoutType::HORIZONTAL)
            ->elements([
                ...$this->buildPlayButton($savedGameId),
                ...$this->buildDeleteButton($savedGameId),
            ])
            ->build();

        return [
            $gameNumber,
            $game['name'] ?? t('games.default_game_name'),
            $game['state'],
            $game['game_user']['role'],
            $game['game_user']['status'],
            $game['game_user']['join_method'],
            $game['game_user']['last_played_at_human'],
            $actionsContainer,
        ];
    }

    private function buildEmptyRow(int $gameNumber): array
    {
        return [
            $gameNumber,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
    }

    private function buildPlayButton(int $gameId): array
    {
        return UIBuilder::button("play_{$gameId}_game")
            ->label(t('games.play_game_action'))
            ->action('play_game', ['game_id' => $gameId])
            ->icon('play')
            ->style('success')
            ->build();
    }

    private function buildDeleteButton(int $gameId): array
    {
        return UIBuilder::button("delete_{$gameId}_game")
            ->label(t('delete_game_action'))
            ->action('delete_game', ['game_id' => $gameId])
            ->icon('trash')
            ->style('danger')
            ->build();
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
