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

    public function play(
        GameApp $gameApp,
        GameInstanceService $gamesService
    ) {
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

    public function event(
        Game $game,
        EventRequestFilter $request,
        IRenderer $renderer,
    ) {
        return response()->json(
            $renderer->render($game, $request->eventInfo())
        );
    }

    public function res(
        GameApp $gameApp,
        string|null $resourceName
    ) {
        $path = app_path("GameApps/$gameApp->prefix/resources/$resourceName");
        return response()->file($path);
    }

    public function publicRes(
        GameApp $gameApp,
        string|null $resourceName
    ) {
        // check if the resource definition exists
        $path = app_path("GameApps/$gameApp->prefix/resources/public/$resourceName.json");
        if (file_exists($path)) {
            $resource = json_decode(file_get_contents($path), true);
            $ext = $resource['ext'];
            $path = app_path("GameApps/$gameApp->prefix/resources/public/$resourceName.$ext");
            if (file_exists($path)) {
                return response()->file($path);
            }
            $path = app_path("GameApps/$gameApp->prefix/resources/public/._$resourceName.$ext");
            if (!file_exists($path)) {
                ImageUtils::fakeImage($path, $resource);
            }
            return response()->file($path);
        }
    }
}
