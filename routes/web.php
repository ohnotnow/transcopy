<?php

Route::get('/', 'HomeController@index')->name('home');

Route::group(['prefix' => '/api'], function () {
});
