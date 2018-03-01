<?php

Route::get('/', 'HomeController@index')->name('home');

Route::group(['prefix' => '/api'], function () {
    Route::get('/torrents', 'Api\TorrentController@index')->name('api.torrent.index');
    Route::get('/torrents/{id}', 'Api\TorrentController@show')->name('api.torrent.show');
    Route::delete('/torrents/{id}/clear-flags', 'Api\TorrentFlagsController@destroy')->name('api.torrent.clear_flags');
    Route::post('/copy/torrents', 'TorrentJobController@store')->name('api.torrent.copy');
    Route::post('/refresh/torrents', 'Api\TorrentController@update')->name('api.torrent.refresh');
});
