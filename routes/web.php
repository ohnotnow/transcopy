<?php

Route::get('/', 'HomeController@index')->name('home');

Route::post('/copy', 'CopyController@store')->name('copy.store');

Route::get('/refresh', 'FileController@update')->name('refresh');
