<?php

use Illuminate\Support\Facades\Route;

Route::post('/api/delfosti/massive-uploader/', 'Delfosti\Massive\Controllers\MassiveController@create');
Route::patch('/api/delfosti/massive-uploader/', 'Delfosti\Massive\Controllers\MassiveController@update');
Route::delete('/api/delfosti/massive-uploader/', 'Delfosti\Massive\Controllers\MassiveController@delete');
