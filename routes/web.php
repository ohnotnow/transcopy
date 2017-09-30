<?php

Route::get('/', 'TorrentController@index')->name('home');

Route::get('/files', 'FileController@index')->name('file.index');
Route::get('/files/refresh', 'FileController@update')->name('file.refresh');
Route::post('/files/copy', 'FileJobController@store')->name('file.copy');

Route::get('/torrents', 'TorrentController@index')->name('torrent.index');
Route::get('/torrents/refresh', 'TorrentController@update')->name('torrent.refresh');
Route::post('/torrents/copy', 'TorrentJobController@store')->name('torrent.copy');
