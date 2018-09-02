<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/torrents', 'Api\TorrentController@index')->name('api.torrent.index');
Route::get('/torrent/{id}', 'Api\TorrentController@show')->name('api.torrent.show');
Route::delete('/torrent/{id}/clear-flags', 'Api\TorrentFlagsController@destroy')->name('api.torrent.clear_flags');
Route::post('/copy', 'TorrentJobController@store')->name('api.torrent.copy');
Route::get('/refresh', 'Api\TorrentController@update')->name('api.torrent.refresh');
