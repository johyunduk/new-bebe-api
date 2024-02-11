<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('join', [AuthController::class, 'join'])
        ->withoutMiddleware(['auth:sanctum']);
    Route::post('login', [AuthController::class, 'login'])
        ->withoutMiddleware(['auth:sanctum']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('tokenRefresh', [AuthController::class, 'refresh']);
});

Route::prefix('diary')->group(function () {
    Route::prefix('/')->group(function () {

    });
});

Route::prefix('mall')->group(function () {

});

Route::prefix('profile')->group(function () {

});
