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

        $container = UIBuilder::container()
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title(t('games.game_lobby_title', ['name' => $gameApp->name]));

        // Build and add UI elements to the container using the tree structure
        $this->buildUIElements($container, $canCreateNewGame, $maxInstances, $saved_games);

        return $container->build();
    }

    /**
     * Build and add UI elements to the container
     * 
     * @param \App\Services\UI\Components\ContainerBuilder $container The container to add elements to
     * @param bool $canCreateNewGame Whether user can create a new game
     * @param int $maxInstances Maximum instances per user
     * @param array $saved_games Array of saved games
     * @return void
     */
    private function buildUIElements($container, bool $canCreateNewGame, int $maxInstances, array $saved_games): void
    {
        // New Game Button
        $container->add(
            UIBuilder::button()
                ->label(t('new_game_button_label'))
                ->action('create_new_game')
                ->icon('plus')
                ->style('primary')
                ->enabled($canCreateNewGame)
                ->tooltip($canCreateNewGame ? t('new_game_button_tooltip') : t('cannot_create_new_game'))
        );

        // Warning Message (only if needed)
        $container->add(
            UIBuilder::label()
                ->text(t('instances_limit_reached', ['max' => $maxInstances]))
                ->style('warning')
                ->visible(!$canCreateNewGame)
        );

        // Saved Games Table
        $container->add(
            $this->buildSavedGamesTable($saved_games, $maxInstances)
        );
    }

    /**
     * Build the saved games table
     * 
     * @param array $saved_games Array of saved games
     * @param int $maxInstances Maximum instances per user
     * @return \App\Services\UI\Components\TableBuilder The table component
     */
    private function buildSavedGamesTable(array $saved_games, int $maxInstances)
    {
        $rows = $this->buildTableRows($saved_games, $maxInstances);

        return UIBuilder::table()
            ->title(count($saved_games) > 0 ? t('games.saved_games_title') : t('no_saved_games_title'))
            ->addHeader(t('games.saved_game_number_column'))
            ->addHeader(t('games.saved_game_name_column'))
            ->addHeader(t('games.saved_game_state_column'))
            ->addHeader(t('games.saved_game_role_column'))
            ->addHeader(t('games.saved_game_status_column'))
            ->addHeader(t('games.saved_game_join_method_column'))
            ->addHeader(t('games.saved_game_last_played_column'))
            ->addHeader(t('games.saved_game_actions_column'), 'game_actions', width: '200px')
            ->rows($rows);
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

    /**
     * Build a row for a saved game
     * 
     * @param array $game Game data
     * @param int $gameNumber Row number
     * @return array Row data with cells
     */
    private function buildGameRow(array $game, int $gameNumber): array
    {
        $savedGameId = $game['id'] ?? null;

        // Build actions container using the new tree API
        $actionsContainer = UIBuilder::container("saved_game_{$savedGameId}_actions")
            ->layout(LayoutType::HORIZONTAL);
        
        // Add buttons to the container
        $actionsContainer->add($this->buildPlayButton($savedGameId));
        $actionsContainer->add($this->buildDeleteButton($savedGameId));

        return [
            $gameNumber,
            $game['name'] ?? t('games.default_game_name'),
            $game['state'],
            $game['game_user']['role'],
            $game['game_user']['status'],
            $game['game_user']['join_method'],
            $game['game_user']['last_played_at_human'],
            $actionsContainer->build(), // Convert to array for table cell
        ];
    }

    /**
     * Build an empty row
     * 
     * @param int $gameNumber Row number
     * @return array Empty row data
     */
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

    /**
     * Build a play button for a game
     * 
     * @param int $gameId Game ID
     * @return \App\Services\UI\Components\ButtonBuilder Button component
     */
    private function buildPlayButton(int $gameId)
    {
        return UIBuilder::button("play_{$gameId}_game")
            ->label(t('games.play_game_action'))
            ->action('play_game', ['game_id' => $gameId])
            ->icon('play')
            ->style('success');
    }

    /**
     * Build a delete button for a game
     * 
     * @param int $gameId Game ID
     * @return \App\Services\UI\Components\ButtonBuilder Button component
     */
    private function buildDeleteButton(int $gameId)
    {
        return UIBuilder::button("delete_{$gameId}_game")
            ->label(t('delete_game_action'))
            ->action('delete_game', ['game_id' => $gameId])
            ->icon('trash')
            ->style('danger');
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
