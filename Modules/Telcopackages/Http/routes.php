<?php

Route::group(['middleware' => ['web', 'lookup:user', 'auth:user'], 'namespace' => 'Modules\Telcopackages\Http\Controllers'], function()
{
    Route::resource('telcopackages', 'TelcopackagesController');
    Route::post('telcopackages/bulk', 'TelcopackagesController@bulk');
    Route::get('api/telcopackages', 'TelcopackagesController@datatable');
});

Route::group(['middleware' => 'api', 'namespace' => 'Modules\Telcopackages\Http\ApiControllers', 'prefix' => 'api/v1'], function()
{
    Route::resource('telcopackages', 'TelcopackagesApiController');
});
