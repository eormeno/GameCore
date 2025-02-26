<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameApp;
use App\Utils\ImageUtils;
use App\Contracts\IRenderer;
use App\Services\GameInstanceService;
use App\Http\Requests\EventRequestFilter;

class GameAppController extends Controller
{

    public function all()
    {
        $gameApps = GameApp::where('active', true)->get(['id', 'prefix', 'name', 'description', 'card_image']);
        return response()->json(['displaying_games_gallery' => $gameApps, 'is_page' => true, 'is_modal' => false]);
    }

    public function play(GameApp $gameApp, GameInstanceService $gamesService)
    {
        $currentUser = auth()->user();
        $currentGame = $gamesService->getOrCreateUserGame($currentUser, $gameApp);
        return response()->json([
            'game' => [
                'is_page' => true,
                'is_modal' => false,
                'title' => $currentGame->title,
                'eventUrl' => route('event', $currentGame->id),
                'resourcesUrl' => route('res', $gameApp->id),
                'width' => $gameApp->width,
                'height' => $gameApp->height
            ]
        ]);
    }

    public function event(Game $game, EventRequestFilter $request, IRenderer $renderer)
    {
        return response()->json(
            $renderer->render($game, $request->eventInfo())
        );
    }

    public function res(GameApp $gameApp, string|null $resourceName)
    {
        $path = app_path("GameApps/$gameApp->prefix/resources/$resourceName");
        return response()->file($path);
    }

    public function publicRes(GameApp $gameApp, string|null $resourceName)
    {
        $basePath = $this->getResourceBasePath($gameApp, true);
        $path = $this->findResourcePath($basePath, $resourceName);
        if ($path === null) {
            return response()->json(['error' => "Resource $resourceName not found"], 404);
        }
        return response()->file($path);
    }

    private function findResourcePath(string $basePath, string $resourceName): string|null
    {
        $path = $this->getRawResourcePath($basePath, $resourceName);
        if ($path === null) {
            $path = $this->getDefinedResourcePath($basePath, $resourceName);
        }
        return $path;
    }

    private function getRawResourcePath(string $basePath, string $resourceName): string|null
    {
        $path = "$basePath$resourceName";
        return file_exists($path) ? $path : null;
    }

    private function getDefinedResourcePath(string $basePath, string $resourceName): string|null
    {
        $definitionPath = "$basePath$resourceName.json";
        if (!file_exists($definitionPath)) {
            return null;
        }

        $resource = json_decode(file_get_contents($definitionPath), true);
        $ext = $resource['ext'];
        $path = "$basePath.$resourceName.$ext";

        if (!file_exists($path)) {
            $path = "$basePath._$resourceName.$ext";
            if (!file_exists($path)) {
                ImageUtils::fakeImage($path, $resource);
            }
        }

        return $path;
    }

    private function getResourceBasePath(GameApp $gameApp, bool $isPublic = false): string
    {
        $path = app_path("GameApps/$gameApp->prefix/resources/");
        return $isPublic ? "{$path}public/" : $path;
    }
}
