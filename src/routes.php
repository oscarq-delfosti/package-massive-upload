<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/massive-uploader/get-functionalities', 'Delfosti\Massive\Controllers\MassiveController@getFunctionanilities');
Route::get('/api/massive-uploader/get-models', 'Delfosti\Massive\Controllers\MassiveController@getModels');
Route::post('/api/massive-uploader/', 'Delfosti\Massive\Controllers\MassiveController@uploader');

Route::get('/api/massive-uploader-log/show', 'Delfosti\Massive\Controllers\MassiveController@show');
Route::get('/api/massive-uploader-log/get', 'Delfosti\Massive\Controllers\MassiveController@get');
Route::get('/api/massive-uploader-log/list', 'Delfosti\Massive\Controllers\MassiveController@list');
Route::post('/api/massive-uploader-log/', 'Delfosti\Massive\Controllers\MassiveController@create');
