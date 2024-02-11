<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(['result' => 'API OK']);
});

Route::get('image/{path}/{filename}', function (string $path, string $filename) {
    $path = "{$path}/{$filename}";

    return \Illuminate\Support\Facades\Storage::disk('local')->download($path);
});
