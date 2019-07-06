<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'RootController@index');
Route::get('/cdn/{service}/{account}', 'RootController@purge');
Route::post('/ajax/purge', 'Ajax\PurgeController@purge');
Route::post('/ajax/purge_url', 'Ajax\PurgeController@purge_url');
Route::post('/ajax/update', 'Ajax\UpdateController@update');

Route::get('/404', function () {
    return view('404');
});

