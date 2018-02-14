<?php

Route::get('server_pipeline/{alias}/data', ['as' => 'server.pipeline', 'uses' => 'ostheneo\toaster\Controllers\ToasterController@askToDatabase']);


Route::get('toaster/database', 'ostheneo\toaster\Controllers\ToasterDatabaseController@index');