<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\MallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BabyController;

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
    Route::get('/', [DiaryController::class, 'diaryList']);
    Route::get('detail/{diaryId}', [DiaryController::class, 'diaryDetail']);
    Route::post('/', [DiaryController::class, 'createDiary']);
    Route::put('edit/{diaryId}', [DiaryController::class, 'editDiary']);
    Route::delete('delete/{diaryId}', [DiaryController::class, 'deleteDiary']);
    Route::prefix('baby')->group(function () {
        Route::get('/', [BabyController::class, 'babyList']);
        Route::post('/', [BabyController::class, 'createBaby']);
        Route::put('{babyId}', [BabyController::class, 'editBaby']);
        Route::delete('{babyId}', [BabyController::class, 'deleteBaby']);
        Route::post('{babyId}/face', [BabyController::class, 'editBabyFace']);
    });
});

Route::prefix('mall')->group(function () {
    Route::get('size', [MallController::class, 'sizeList']);
    Route::get('category', [MallController::class, 'categoryList']);
    Route::post('category', [MallController::class, 'createCategory']);
    Route::get('item', [MallController::class, 'itemList']);
    Route::post('item', [MallController::class, 'createItem']);
    Route::get('item/{itemId}', [MallController::class, 'itemDetail']);
    Route::post('item/{itemId}', [MallController::class, 'editItem']);
});

Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::put('edit', [ProfileController::class, 'editProfile']);
    Route::post('avatar', [ProfileController::class, 'editAvatar']);
});
