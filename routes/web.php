<?php

Route::get('/', 'TorrentListController@index')->name('home');

Route::get('/files', 'FileController@index')->name('file.index');
Route::get('/files/refresh', 'FileController@update')->name('file.refresh');
Route::post('/files/copy', 'FileJobController@store')->name('file.copy');

Route::get('/torrents', 'TorrentListController@index')->name('torrent.index');
Route::get('/torrents/refresh', 'TorrentListController@update')->name('torrent.refresh');
Route::get('/torrents/{id}/refresh', 'TorrentController@update')->name('torrent.update');
Route::post('/torrents/copy', 'TorrentJobController@store')->name('torrent.copy');
