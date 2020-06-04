<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
  <<<<<<< HEAD
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

Route::namespace('Api')->group(function() {
    Route::get('index', 'IndexController@index');
});

Route::namespace('Api\V4')->group(function() {
    Route::get('/v4/index', 'IndexController@index');
    Route::get('/v4/announce', 'IndexController@announce');
    Route::get('/v4/banner', 'IndexController@banner');
    Route::get('/V4/goods/index', 'MallController@goods_list');
});
