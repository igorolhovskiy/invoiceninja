<?php

Route::group(['middleware' => ['web', 'lookup:user', 'auth:user'], 'namespace' => 'Modules\ImportColt\Http\Controllers'], function()
{
    Route::get('importcolt/test', 'ImportColtController@test');

    Route::resource('importcolt', 'ImportColtController');
    Route::post('importcolt/bulk', 'ImportColtController@bulk');
    Route::get('api/importcolt', 'ImportColtController@datatable');

    Route::post('importcolt/upload', 'ImportColtController@upload');
});

Route::group(['middleware' => 'api', 'namespace' => 'Modules\ImportColt\Http\ApiControllers', 'prefix' => 'api/v1'], function()
{
    Route::resource('importcolt', 'ImportColtApiController');
});
