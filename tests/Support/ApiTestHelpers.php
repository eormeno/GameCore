<?php

namespace Tests\Support;

use Illuminate\Testing\TestResponse;
use App\Services\Screens\GameLobbyScreenStructure;

/**
 * API Testing Helpers
 * 
 * This class provides helper methods for testing API responses
 * with consistent structure assertions.
 */
class ApiTestHelpers
{
    /**
     * Assert game lobby screen response structure
     * 
     * @param TestResponse $response
     * @return void
     */
    public static function assertGameLobbyScreenStructure(TestResponse $response): void
    {
        $response->assertJsonStructure(GameLobbyScreenStructure::response());
    }
}