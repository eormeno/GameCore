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
        $gameApps = GameApp::where('active', true)->get(['id', 'prefix', 'name', 'description', 'card_image']);
        return response()->json(['displaying_games_gallery' => $gameApps, 'is_page' => true, 'is_modal' => false]);
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
        // check if the resource exists
        $path = app_path("GameApps/$gameApp->prefix/resources/public/$resourceName");
        if (!file_exists($path)) {
            // if not, check if the resource exists as a fake generated resource
            $path = app_path("GameApps/$gameApp->prefix/resources/public/._$resourceName");
            if (!file_exists($path)) {
                // if not, create a fake resource
                $this->createFakeResource($path);
            }
        }
        return response()->file($path);
    }

    private function createFakeResource($path)
    {
        // ask if the file name has the pattern: "name.width.height.resourceType"
        $name = pathinfo($path, PATHINFO_BASENAME);
        $parts = explode('.', $name);
        // last part is the resource type
        $resourceType = array_pop($parts);
        // if resource type is not an image, return
        if (in_array($resourceType, ['png', 'jpg', 'jpeg', 'gif'])) {
            $height = array_pop($parts);
            $width = array_pop($parts);
            $name = array_pop($parts);
            $image = imagecreatetruecolor($width, $height);
            imagefill($image, 0, 0, imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255)));
            // add some text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 5, 5, 5, $name, $textColor);

            if ($resourceType === 'png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $path);
            }
            if ($resourceType === 'jpg' || $resourceType === 'jpeg') {
                imagejpeg($image, $path);
            }
            if ($resourceType === 'gif') {
                imagegif($image, $path);
            }

            imagedestroy($image);
            return;
        }
    }


}
