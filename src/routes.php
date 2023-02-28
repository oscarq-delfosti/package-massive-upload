<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/massive-upload/get-actions', 'Delfosti\Massive\Controllers\MassiveController@getActions');
Route::get('/api/massive-upload/get-action', 'Delfosti\Massive\Controllers\MassiveController@getAction');
Route::get('/api/massive-upload/get-models', 'Delfosti\Massive\Controllers\MassiveController@getModels');
Route::post('/api/massive-upload/uploader', 'Delfosti\Massive\Controllers\MassiveController@uploader');

Route::get('/api/massive-upload-log/show', 'Delfosti\Massive\Controllers\MassiveController@show');
Route::get('/api/massive-upload-log/get', 'Delfosti\Massive\Controllers\MassiveController@get');
Route::get('/api/massive-upload-log/list', 'Delfosti\Massive\Controllers\MassiveController@list');
