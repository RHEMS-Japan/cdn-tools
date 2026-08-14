<?php

use App\Http\Controllers\RootController;
use App\Http\Controllers\Ajax\PurgeController;
use App\Http\Controllers\Ajax\UpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [RootController::class, 'index']);
Route::get('/cdn/{service}/{account}', [RootController::class, 'purge']);
Route::post('/ajax/purge', [PurgeController::class, 'purge']);
Route::post('/ajax/purge_url', [PurgeController::class, 'purge_url']);
Route::post('/ajax/update', [UpdateController::class, 'update']);

Route::get('/404', function () {
    return view('404');
});