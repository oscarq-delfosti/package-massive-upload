<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/massive-upload/get-actions', 'Delfosti\Massive\Controllers\MassiveUploadController@getActions');
Route::get('/api/massive-upload/get-action', 'Delfosti\Massive\Controllers\MassiveUploadController@getAction');
Route::get('/api/massive-upload/get-models', 'Delfosti\Massive\Controllers\MassiveUploadController@getModels');
Route::post('/api/massive-upload/uploader', 'Delfosti\Massive\Controllers\MassiveUploadController@uploader');

Route::get('/api/massive-upload-log/show', 'Delfosti\Massive\Controllers\MassiveUploadLogController@show');
Route::get('/api/massive-upload-log/get', 'Delfosti\Massive\Controllers\MassiveUploadLogController@get');
Route::get('/api/massive-upload-log/list', 'Delfosti\Massive\Controllers\MassiveUploadLogController@list');
