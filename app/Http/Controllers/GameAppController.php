<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Game;
use App\Models\GameApp;
use App\Utils\ImageUtils;
use App\Contracts\IRenderer;
use App\Services\GameInstanceService;
use App\Http\Requests\EventRequestFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GameAppController extends Controller
{

    public function all()
    {
        $gameApps = GameApp::where('active', true)->get([
            'id',
            'prefix',
            'name',
            'description',
            'card_image',
            'prefab_name',
            'max_instances_per_user',
            'min_users_per_instance',
            'max_users_per_instance',
        ]);
        return response()->json(['displaying_games_gallery' => $gameApps]);
    }

    public function play(int $gameAppId, GameInstanceService $gamesService)
    {
        try {
            $currentUser = auth()->user();
            $gameApp = GameApp::findOrFail($gameAppId);
            $currentGame = $gamesService->getOrCreateUserGame($currentUser, $gameApp);
            return response()->json([
                'game' => [
                    'title' => $currentGame->title,
                    'eventUrl' => route('event', $currentGame->id),
                    'resourcesUrl' => route('res', $gameApp->id),
                    'width' => $gameApp->width,
                    'height' => $gameApp->height,
                    'invitationCode' => $currentGame->invitation_code,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['game_not_found' => ['message' => 'Game not found']]);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function playGame(GameApp $game, GameInstanceService $gamesService)
    {
        $currentUser = auth()->user();
        dd($game);
        // $currentGame = $gamesService->getOrCreateUserGame($currentUser, $game);
        // return response()->json([
        //     'game' => [
        //         'title' => $currentGame->title,
        //         'eventUrl' => route('event', $currentGame->id),
        //         'resourcesUrl' => route('res', $game->id),
        //         'width' => $game->width,
        //         'height' => $game->height
        //     ]
        // ]);
    }

    public function event(Game $game, EventRequestFilter $request, IRenderer $renderer)
    {
        return response()->json(
            $renderer->render($game, $request->eventInfo())
        );
    }

    public function res(GameApp $gameApp, string|null $resourceName)
    {
        // $path = app_path("GameApps/$gameApp->prefix/resources/$resourceName");
        // return response()->file($path);
        $basePath = $this->resourceBasePath($gameApp);
        $path = $this->findResource($basePath, $resourceName);
        if ($path === null) {
            return response()->json(['error' => "Resource $resourceName not found"], 404);
        }
        return response()->file(
            $path,
            [
                'Cache-Control' => 'public, max-age=300',
                'Pragma' => 'public',
                'Expires' => '60'   // 1 minute
            ]
        );
    }

    public function publicRes(GameApp $gameApp, string|null $resourceName)
    {
        $basePath = $this->resourceBasePath($gameApp, true);
        $path = $this->findResource($basePath, $resourceName);
        if ($path === null) {
            return response()->json(['error' => "Resource $resourceName not found"], 404);
        }
        return response()->file(
            $path,
            [
                'Cache-Control' => 'public, max-age=300',
                'Pragma' => 'public',
                'Expires' => '15'   // 15 seconds
            ]
        );
    }

    private function findResource(string $basePath, string $resourceName): string|null
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
            $definitionPath = $this->createDefinedResource($basePath, $resourceName);
        }
        $definitionFileTime = $this->getFileCreationTime($definitionPath);

        $resource = json_decode(file_get_contents($definitionPath), true);
        $ext = $resource['ext'];
        $path = "$basePath$resourceName.$ext";

        if (!file_exists($path)) {
            $path = "$basePath._$resourceName.$ext";
            if (!file_exists($path) || $this->getFileCreationTime($path) < $definitionFileTime) {
                ImageUtils::fakeImage($path, $resource, $definitionFileTime);
            }
        }

        return $path;
    }

    private function getFileCreationTime(string $path): int
    {
        return filemtime($path);
    }

    private function createDefinedResource(string $basePath, string $resourceName): string
    {
        $ext = pathinfo($resourceName, PATHINFO_EXTENSION) ?: 'png';
        $resource = [
            'ext' => $ext,
            'text' => $resourceName,
            'color' => 'black',
            'fontSize' => 8,
            'width' => 160,
            'height' => 90
        ];
        $definitionPath = "$basePath$resourceName.json";
        // ensure create the file and all its parent directories
        try {
            file_put_contents($definitionPath, json_encode($resource));
        } catch (\Exception $e) {
            mkdir(dirname($definitionPath), 0755, true);
            file_put_contents($definitionPath, json_encode($resource, JSON_PRETTY_PRINT));
        }
        return $definitionPath;
    }

    private function resourceBasePath(GameApp $gameApp, bool $isPublic = false): string
    {
        $path = app_path("GameApps/$gameApp->prefix/resources/");
        return $isPublic ? "{$path}public/" : $path;
    }
}
