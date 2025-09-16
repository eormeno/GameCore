<?php

namespace App\Http\Controllers;

use App\Services\GameAppDebugService;
use Illuminate\Http\JsonResponse;

class DebugController extends Controller
{
    private GameAppDebugService $gameAppService;

    public function __construct(GameAppDebugService $gameAppService)
    {
        $this->gameAppService = $gameAppService;
    }

    /**
     * Get all installed game apps with basic information
     */
    public function getAllGameApps(): JsonResponse
    {
        $onlyActive = request()->query('active', 'false') === 'true';
        $gameApps = $this->gameAppService->getAllGameApps($onlyActive);
        
        return response()->json($gameApps);
    }

    /**
     * Get detailed information for a specific game app
     */
    public function getGameAppDetails(string $gameAppPrefix): JsonResponse
    {
        $gameAppDetails = $this->gameAppService->getGameAppDetails($gameAppPrefix);
        
        if (!$gameAppDetails) {
            return response()->json(['message' => 'Game App not found'], 404);
        }
        
        return response()->json($gameAppDetails);
    }
}