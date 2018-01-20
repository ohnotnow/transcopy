<?php

Route::get('/', 'TorrentController@index')->name('torrent.index');

Route::get('/api/torrents', 'Api\TorrentController@index')->name('api.torrent.index');
Route::get('/api/torrents/{id}', 'Api\TorrentController@show')->name('api.torrent.show');
Route::delete('/api/torrents/{id}/clear-flags', 'Api\TorrentFlagsController@destroy')->name('api.torrent.clear_flags');
Route::post('/api/copy/torrents', 'TorrentJobController@store')->name('api.torrent.copy');
Route::post('/api/refresh/torrents', 'Api\TorrentController@update')->name('api.torrent.refresh');