<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameApp;
use App\Contracts\IRenderer;
use App\Services\GameInstanceService;
use App\Http\Requests\EventRequestFilter;

class GameAppController extends Controller
{

    public function all()
    {
        $gameApps = GameApp::where('active', true)->get(['prefix', 'name', 'description', 'image']);
        return response()->json($gameApps);
    }

    public function play(
        GameApp $gameApp,
        GameInstanceService $gamesService
    ) {
        $currentUser = auth()->user();
        $currentGame = $gamesService->getOrCreateUserGame($currentUser, $gameApp);
        //return view("game-app.$gameApp->client", compact('gameApp', 'currentGame'));
        return response()->json([
            'game' => [
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
}
