<?php

use App\Models\Game;
use App\Models\User;
use App\Models\GameApp;

function reloadGameApps(string $prefix): GameApp
{
	Artisan::call('games');
	$gameApp = GameApp::where('prefix', $prefix)->firstOrFail();
	test()->assertNotNull($gameApp);
	return $gameApp;
}

function loginUser(): User
{
	$user = User::factory()->adminUser()->create();
	Auth::login($user);
	return $user;
}

function setupGameApp(string $prefix): GameApp
{
	$user = loginUser();
	$gameApp = reloadGameApps($prefix);
	return $gameApp;
}

function userShowGameApp(string $prefix): void
{
	// $gameApp = setupGameApp($prefix);
	// $response = test()->get(route('dashboard'));
	// $response->assertStatus(200);
	// $response->assertSee($gameApp->name);
}

function getUserPlayingGame(string $prefix): Game
{
	$gameApp = setupGameApp($prefix);
	$response = test()->get(route('play', $gameApp));
	if ($response->exception) {
		throw $response->exception;
	}
	$response->assertStatus(200);
	// the game is created
	$newGame = Game::where('game_app_id', $gameApp->id)->first();
	test()->assertNotNull($newGame);
	return $newGame;
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
