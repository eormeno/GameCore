<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 1, 'message' => 'GameCore Engine API Running']);
});
