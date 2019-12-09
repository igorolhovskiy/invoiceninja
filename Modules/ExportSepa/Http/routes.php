<?php

Route::group(['middleware' => ['web', 'lookup:user', 'auth:user'], 'namespace' => 'Modules\ExportSepa\Http\Controllers'], function()
{
    Route::resource('exportsepa', 'ExportSepaController');
    Route::post('exportsepa/bulk', 'ExportSepaController@bulk');
    Route::get('api/exportsepa', 'ExportSepaController@datatable');
    Route::get('api/exportsepa/{exportsepa}/items', 'ExportSepaController@itemsDatatable');
    Route::get('exportsepa/{exportsepa}/generate-sepa', 'ExportSepaController@generateSepaXml');
});

Route::group(['middleware' => 'api', 'namespace' => 'Modules\ExportSepa\Http\ApiControllers', 'prefix' => 'api/v1'], function()
{
    Route::resource('exportsepa', 'ExportSepaApiController');
});
