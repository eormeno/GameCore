<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameAppController;
use App\Http\Controllers\DatabaseTableController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ImageUploadController;

Route::get('/', fn() => response()->json(['status' => 1]))->name('root');

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/game-app', [GameAppController::class, 'all'])->name('all');
Route::get('/game-app/{gameApp}/public/{resourceName?}', [GameAppController::class, 'publicRes'])->name('public');

// Image upload routes
Route::post('/ui-upload', [ImageUploadController::class, 'upload'])->name('ui.upload');
Route::post('/ui-upload/confirm', [ImageUploadController::class, 'confirm'])->name('ui.upload.confirm');
Route::post('/ui-upload/cancel', [ImageUploadController::class, 'cancel'])->name('ui.upload.cancel');
Route::delete('/ui-upload', [ImageUploadController::class, 'delete'])->name('ui.upload.delete');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/game-app/{gameApp}/play/{invitationCode?}', [GameAppController::class, 'play'])->name('play');
    Route::post('/game-app/{gameApp}/ui/new', [GameAppController::class, 'newGameUI'])->name('new_ui');
    Route::get('/game-app/{gameApp}/res/{resourceName?}', [GameAppController::class, 'res'])->name('res');
    Route::post('/game-app/{game}', [GameAppController::class, 'event'])->name('event');
});

// Database Tables endpoints
Route::get('/tables', [DatabaseTableController::class, 'index']);

// Debug endpoints
Route::get('/debug/game-apps', [DebugController::class, 'getAllGameApps']);
Route::get('/debug/game-apps/{gameAppPrefix}', [DebugController::class, 'getGameAppDetails']);
