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

    //首页
    Route::get('/v4/index/announce', 'IndexController@announce');
    Route::get('/v4/index/banner', 'IndexController@banner');
    Route::get('/v4/index/live', 'IndexController@live');
    Route::get('/v4/index/column', 'IndexController@column');
    Route::get('/v4/index/works', 'IndexController@works');
    Route::get('/v4/index/wiki', 'IndexController@wiki');
    Route::get('/v4/index/book', 'IndexController@book');
    Route::get('/v4/index/goods', 'IndexController@goods');

    //专栏
    Route::get('/v4/column/get_column_list', 'ColumnController@getColumnList');
    Route::get('/v4/column/get_column_detail', 'ColumnController@getColumnDetail');
    Route::get('/v4/column/get_column_works', 'ColumnController@getColumnWorks');
    Route::get('/v4/column/collection', 'ColumnController@Collection');
    Route::get('/v4/column/get_recommend', 'ColumnController@getRecommend');


    //课程
    Route::get('/v4/works/get_works_detail', 'WorksController@getWorksDetail');
    Route::get('/v4/works/show', 'WorksController@show');
    Route::get('/v4/works/edit_history_time', 'WorksController@editHistoryTime');
    Route::get('/v4/works/get_works_category', 'WorksController@getWorksCategoryTeacher');
    Route::get('/v4/works/get_works_content', 'WorksController@getWorksContent');
    Route::get('/v4/works/get_works_index', 'WorksController@getWorksIndex');

    //下单
    Route::get('/v4/order/create_column_order', 'OrderController@createColumnOrder');
    Route::get('/v4/order/create_works_order', 'OrderController@createWorksOrder');
    Route::get('/v4/order/get_coupon', 'OrderController@getCoupon');

    
    //商城部分
    Route::get('/V4/goods/info', 'MallController@goodsList');
    Route::get('/V4/goods/coupon_list', 'MallController@couponList');
    Route::get('/V4/goods/comment_list', 'MallController@commentList');
    Route::get('/V4/goods/category_list', 'MallController@categoryList');
});
