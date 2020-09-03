<?php

Route::group(['middleware' => ['web', 'lookup:user', 'auth:user'], 'namespace' => 'Modules\Telcorates\Http\Controllers'], function()
{
    Route::get('telcorates/check_name/{id?}', 'TelcoratesController@checkName');      
    Route::resource('telcorates', 'TelcoratesController');
    Route::post('telcorates/bulk', 'TelcoratesController@bulk');            
    Route::get('api/telcorates', 'TelcoratesController@datatable');
    Route::get('api/telcorates/{telcorates}/telcocodes', 'TelcoratesController@getCodes');
    Route::post('api/telcorates/{telcorates}/telcocodes', 'TelcoratesController@addCode');
    Route::post('api/telcorates/{telcorates}/telcocodes/bulk-upload', 'TelcoratesController@bulkUploadCodes');
    Route::put('api/telcorates/{telcorates}/telcocodes', 'TelcoratesController@updateCode');
    Route::delete('api/telcorates/{telcorates}/telcocodes', 'TelcoratesController@deleteCode');
});

Route::group(['middleware' => 'api', 'namespace' => 'Modules\Telcorates\Http\ApiControllers', 'prefix' => 'api/v1'], function()
{
    Route::resource('telcorates', 'TelcoratesApiController');
});
