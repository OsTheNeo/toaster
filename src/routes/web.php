<?php
/**
 * Created by PhpStorm.
 * User: OsTheNeo
 * Date: 13/02/2018
 * Time: 3:34 PM
 */

Route::get('server_pipeline/{alias}/data', ['as' => 'server.pipeline', 'uses' => 'OsTheNeo\Toaster\Controllers\ToasterController@askToDatabase']);

Route::post('gallery/upload', ['as'=>'gallery.upload',  'uses' => 'OsTheNeo\Toaster\Controllers\ToasterController@galleryUpload']);
Route::post('gallery/remove', ['as'=>'gallery.remove',  'uses' => 'OsTheNeo\Toaster\Controllers\ToasterController@galleryRemove']);
Route::post('gallery/sort',   ['as'=>'gallery.sort',    'uses' => 'OsTheNeo\Toaster\Controllers\ToasterController@gallerySort']);