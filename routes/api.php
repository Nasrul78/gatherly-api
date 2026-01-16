<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
            Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:20,1');
        });
    });
});
