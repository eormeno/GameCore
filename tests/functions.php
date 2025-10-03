<?php

use App\Models\Game;
use App\Models\User;
use App\Models\GameApp;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

function reloadGameApps(): void
{
    Artisan::call('games');
}

function findGameApp(string $prefix): GameApp
{
    $gameApp = GameApp::where('prefix', $prefix)->first();
    test()->assertNotNull($gameApp);
    return $gameApp;
}

function seedTestUsers(): array
{
    $testUsers = [];
    Artisan::call('db:seed', ['--class' => 'UsersSeeder']);
    $seedUsers = User::all();
    foreach ($seedUsers as $user) {
        $testUsers[] = $user;
    }
    return $testUsers;
}

function loginUser(User $user): void
{
    Auth::login($user);
}

function loginTestUser(int $index = 0): User
{
    $testUsers = seedTestUsers();
    if (!isset($testUsers[$index])) {
        throw new Exception("No test user at index {$index}");
    }
    $user = $testUsers[$index];
    Auth::login($user);
    return $user;
}

function adminUserCredentials(): array
{
    $adminEmail = env('ADMIN_EMAIL', '');
    $adminPassword = env('ADMIN_PASSWORD', '');
    return ['email' => $adminEmail, 'password' => $adminPassword];
}

function write($response): void
{
    $content = $response->getContent();
    $decoded = json_decode($content);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo PHP_EOL . "Raw response: " . $content . PHP_EOL;
        return;
    }

    echo PHP_EOL . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

function setupGameApp(string $prefix): GameApp
{
    reloadGameApps();
    $testUsers = seedTestUsers();
    loginUser($testUsers[0]);
    $gameApp = findGameApp($prefix);
    return $gameApp;
}

function userShowGameApp(string $prefix): void
{
    // $gameApp = setupGameApp($prefix);
    // $response = test()->get(route('dashboard'));
    // $response->assertStatus(200);
    // $response->assertSee($gameApp->name);
}

function userWantsToPlayTheGameApplication(GameApp $gameApp): void
{
    $response = test()->get(route('play', $gameApp));
    if ($response->exception) {
        throw $response->exception;
    }
    $response->assertStatus(200);
}

function getUserPlayingGame(string $prefix): Game
{
    $gameApp = setupGameApp($prefix);
    // The user wants to play a game application
    $response = test()->get(route('play', $gameApp));
    if ($response->exception) {
        throw $response->exception;
    }
    $response->assertStatus(200);
    // the game instance is created for the user
    $newGame = Game::where('game_app_id', $gameApp->id)->first();
    test()->assertNotNull($newGame);
    return $newGame;
}

function findUserGameInstance(GameApp $gameApp): Game
{
    $user = Auth::user();
    $game = Game::where('game_app_id', $gameApp->id)
        ->where('owner_id', $user->id)
        ->first();
    test()->assertNotNull($game);
    return $game;
}

function showTable($table, $columns = [], $limit = 100)
{
    Artisan::call('db:show', [
        'table' => $table,
        'columns' => $columns,
        '--limit' => $limit
    ]);
    $consoleOutput = Artisan::output();
    echo PHP_EOL . $consoleOutput;
}

function createEvent(string $name, array $data = [], array $rendered = []): array
{
    $event = [
        'event' => $name,
        'source' => 'test',
        'data' => $data,
        'destination' => null,
        'rendered' => $rendered,
    ];
    return $event;
}

function renderedIds($response, array $keysToRemove = ['elapsed', 'root', 'actives', 'deactives']): array
{
    $data = $response->getContent();
    $dataKeys = array_keys(json_decode($data, true));
    foreach ($keysToRemove as $key) {
        if (in_array($key, $dataKeys)) {
            unset($dataKeys[array_search($key, $dataKeys)]);
        }
    }
    $onlyKeysAsArray = array_values($dataKeys);
    return $onlyKeysAsArray;
}
