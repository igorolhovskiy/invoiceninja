<?php

Route::group(['middleware' => ['web', 'lookup:user', 'auth:user'], 'namespace' => 'Modules\Telcorates\Http\Controllers'], function()
{
    Route::get('telcorates/check_name/{id?}', 'TelcoratesController@checkName');      
    Route::resource('telcorates', 'TelcoratesController');
    Route::post('telcorates/bulk', 'TelcoratesController@bulk');            
    Route::get('api/telcorates', 'TelcoratesController@datatable');
});

Route::group(['middleware' => 'api', 'namespace' => 'Modules\Telcorates\Http\ApiControllers', 'prefix' => 'api/v1'], function()
{
    Route::resource('telcorates', 'TelcoratesApiController');
});
