<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameAppController;
use App\Http\Controllers\LogViewerController;

// Log viewer routes
Route::prefix('logs')->group(function () {
    Route::get('/', [LogViewerController::class, 'index'])->name('logs.index');
    Route::get('/content', [LogViewerController::class, 'getContent'])->name('logs.content');
    Route::get('/download', [LogViewerController::class, 'download'])->name('logs.download');
    Route::post('/clear', [LogViewerController::class, 'clear'])->name('logs.clear');
});

// Route::get('/', fn() => response()->file(public_path('index.html')));
Route::get('/{any}', function () {
    return response()->file(public_path('index.html'));
})->where('any', '^(?!api)(?!.*\.[a-z0-9]+$).*');

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
