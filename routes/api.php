<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api\V4')->group(function(){
    Route::get('index', 'IndexController@index');
    Route::get('column/index', 'ColumnController@index');
    Route::get('column/get_list', 'ColumnController@get_list');

//    Route::match(['get','post'],'column/get_list', 'ColumnController@get_list');

});
