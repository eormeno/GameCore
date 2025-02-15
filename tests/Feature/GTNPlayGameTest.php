<?php

test("The user can see the game in his dashboard", function () {
    userShowGameApp('gtn');
});

test("Clicking 'play' game page is shown", function () {
    getUserPlayingGame('gtn');
});

test("The game's root gameobject is created", function () {
    $newGame = getUserPlayingGame('gtn');
	$rootPrefab = existsRootPrefab('gtn');
    $rootGameObject = rootGameObjectIsCreated($newGame);
    gameObjectHasComponents($rootPrefab, $rootGameObject);
	// showTable('game_objects', ['id', 'name', 'active', 'game_object_id', 'state']);
	// showTable('components');
});

test("Events interaction returns the active gameobject with its view", function () {
	//$this->markTestSkipped('Only for debugging');
    $newGame = getUserPlayingGame('gtn');
    $game_app = $newGame->gameApp;
    $prefab_name = $game_app->prefab_name;

    // Reload the game (this must be the first event)
    $event = createEvent('reload');
    $response = $this->postJson(route('event', $newGame), $event);
    if ($response->exception) {
        throw $response->exception;
    }
    $response->assertStatus(200);
    /*
     * The expected json should have the following structure
     * {
     *     "root": 1,
     *     "1":  // a base 64 encoded string
     *     "actives": // an array with the active game objects, in this case "1"
     * }
     */
    $response->assertJsonStructure(['root', '1', 'actives',]);
    $this->assertIsString($response->json('1'));
    $this->assertIsArray($response->json('actives'));
    // The game should be in 'initial' state
    existsGameObjectsInDatabase([
        1 => [$prefab_name, $newGame->id, null, 'initial'],
    ]);

    // The user wants to play (a button is clicked that sends this event)
    $event = createEvent('want_to_play');
    $response = $this->postJson(route('event', $newGame), $event);
    $response->assertStatus(200);
    // The game should be in 'showing_clues' state
    existsGameObjectsInDatabase([
        1 => [$prefab_name, $newGame->id, null, 'showing_clue'],
    ]);

    // The user wants to play (a button is clicked that sends this event)
    $response = $this->postJson(route('event', $newGame), $event);
    $response->assertStatus(200);
    existsGameObjectsInDatabase([
        1 => [$prefab_name, $newGame->id, null, 'playing'],
    ]);

    // In test environment the random number is always 512
    $event = createEvent('guess', ['number' => 512]);
    $response = $this->postJson(route('event', $newGame), $event);
    $response->assertStatus(200);
    // The game should be in the 'success' state
    existsGameObjectsInDatabase([
        1 => [$prefab_name, $newGame->id, null, 'success'],
    ]);
	$this->withoutMockingConsoleOutput();
    // showTable('game_objects', ['id', 'name', 'active', 'game_object_id', 'state']);
});

// test('Display tables', function () {
//     //$this->markTestSkipped('Only for debugging');
//     getUserPlayingGame('gtn');
//     $this->withoutMockingConsoleOutput();
//     showTable('prefabs', ['name', 'type']);
//     showTable('game_objects', ['id', 'name', 'active', 'game_object_id', 'state']);
//     showTable('components');
//     //showTable('game_services');
// });
