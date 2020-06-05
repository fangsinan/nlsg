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


Route::namespace('Api\V4')->group(function() {
    Route::get('/v4/index', 'IndexController@index');
    Route::get('/v4/index/announce', 'IndexController@announce');
    Route::get('/v4/index/banner', 'IndexController@banner');
    Route::get('/v4/index/live', 'IndexController@live');
    Route::get('/v4/index/column', 'IndexController@column');
    Route::get('/v4/index/works', 'IndexController@works');
    Route::get('/V4/goods/index', 'MallController@goods_list');
    //专栏
    Route::get('/v4/column/get_column_list', 'ColumnController@getColumnList');
    Route::get('/v4/column/get_column_detail', 'ColumnController@getColumnDetail');
    //课程
    Route::get('/v4/works/get_works_detail', 'WorksController@getWorksDetail');
    Route::get('/v4/works/show', 'WorksController@show');
    Route::get('/v4/works/edit_history_time', 'WorksController@editHistoryTime');

});
