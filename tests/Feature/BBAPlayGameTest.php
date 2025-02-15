<?php

test("The user can see the game in his dashboard", function () {
	userShowGameApp('bba');
});

test("Clicking 'play' game page is shown", function () {
	getUserPlayingGame('bba');
});

test("The game's root gameobject is created", function () {
	$newGame = getUserPlayingGame('bba');
	$rootPrefab = existsRootPrefab('bba');
	$rootGameObject = rootGameObjectIsCreated($newGame);
	gameObjectHasComponents($rootPrefab, $rootGameObject);
	// showTable('game_objects', ['id', 'name', 'active', 'active_parents', 'game_id', 'game_object_id', 'state']);
	// showTable('components');
	// showTable('label_components');
	// showTable('button_components');
	// showTable('container_components');
});

test("Events interaction returns the active gameobject with its view", function () {
	$newGame = getUserPlayingGame('bba');

	// Reload the game (this must be the first event)
	$event = createEvent('reload');
	$response = $this->postJson(route('event', $newGame), $event);
	$response->assertStatus(200);
	$rendered = renderedIds($response);
	// echo json_encode(json_encode($rendered), JSON_PRETTY_PRINT) . PHP_EOL;
	// echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);

	$event = createEvent(name: 'start', rendered: $rendered);
	$response = $this->postJson(route('event', $newGame), $event);
	if ($response->exception) {
        throw $response->exception;
    }
	$response->assertStatus(200);
	// echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);

	// showTable('game_objects', ['id', 'name', 'active', 'active_parents', 'game_id', 'game_object_id', 'state']);
	showTable('game_app_events', ['id', 'name']);
	showTable('game_event_listener_managers');
	showTable('event_listeners');

	/*
		   * The expected json should have the following structure
		   * {
		   *     "root": 1,
		   *     "1":  // a base 64 encoded string
		   *     "actives": // an array with the active game objects, in this case "1"
		   * }
		   *-/
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
		  showTable('game_objects', ['id', 'name', 'active', 'game_object_id', 'state']);
		  */
});

// test('Display tables', function () {
//     //$this->markTestSkipped('Only for debugging');
//     getUserPlayingGame('bba');
//     $this->withoutMockingConsoleOutput();
//     showTable('prefabs', ['name', 'type']);
//     showTable('game_objects', ['id', 'name', 'active', 'game_object_id', 'state']);
//     showTable('components');
//     //showTable('game_services');
// });
