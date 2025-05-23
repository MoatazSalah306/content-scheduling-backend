<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    // Basic profile Management ( show and update)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    
    Route::apiResource('posts', PostController::class);
    Route::get('/platforms', [PlatformController::class, 'index']);
    Route::post('/platforms/toggle', [PlatformController::class, 'toggle']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
