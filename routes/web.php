<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameAppController;

Route::get('/', fn() => response()->file(public_path('index.html')));

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game/{prefix}/play', [GameAppController::class, 'playGame'])
        ->where('prefix', '[a-z]{3}');
});

Route::get('/storage/images/{filename}', function ($filename) {
    $path = storage_path("app/public/images/$filename");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
