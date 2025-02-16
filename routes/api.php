<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameAppController;

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/login', [AuthController::class, 'loginUI'])->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/game-app', [GameAppController::class, 'all'])->name('all');

// Ruta protegida (ejemplo)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/game-app/{gameApp}/play', [GameAppController::class, 'play'])->name('play');
    Route::get('/game-app/{gameApp}/res/{resourceName?}', [GameAppController::class, 'res'])->name('res');
    Route::post('/game-app/{game}', [GameAppController::class, 'event'])->name('event');
});
