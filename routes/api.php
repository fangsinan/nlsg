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
    Route::get('/v4/column/get_lecture_list', 'ColumnController@getLectureList');
    Route::get('/v4/column/get_lecture_study_list', 'ColumnController@LectureStudyList');


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


    //*******************************商城部分开始*******************************
    Route::get('/V4/goods/info', 'MallController@goodsList');
    Route::get('/V4/goods/coupon_list', 'MallController@couponList');
    Route::get('/V4/goods/comment_list', 'MallController@commentList');
    Route::get('/V4/goods/category_list', 'MallController@categoryList');
    Route::get('/V4/goods/banner_list', 'MallController@bannerList');
    Route::get('/V4/goods/home_sp_list', 'MallController@homeSpList');
    Route::get('/V4/goods/flash_sale', 'MallController@flashSaleList');
    Route::get('/V4/goods/group_buy', 'MallController@groupBuyList');
    Route::get('/V4/goods/service_description', 'MallController@mallServiceDescription');
    Route::post('/V4/goods/get_coupon', 'CouponController@getCoupon');

    Route::get('/V4/address/get_data', 'AddressController@getData');
    Route::post('/V4/address/create', 'AddressController@create');
    Route::get('/V4/address/get_list', 'AddressController@getList');
    Route::put('/V4/address/status_change', 'AddressController@statusChange');
    Route::get('/V4/address/list_of_shop', 'AddressController@listOfShop');

    Route::post('/V4/shopping_cart/create', 'ShoppingCartController@create');
    Route::get('/V4/shopping_cart/get_list', 'ShoppingCartController@getList');
    Route::put('/V4/shopping_cart/status_change', 'ShoppingCartController@statusChange');

    Route::get('/V4/mall/prepare_create_rder', 'MallOrderController@prepareCreateOrder');
    Route::get('/V4/mall/create_rder', 'MallOrderController@createOrder');

    //*******************************商城部分结束*******************************

    //想法
    Route::get('/v4/comment/index', 'CommentController@index');
    Route::post('/v4/comment/store', 'CommentController@store');
    Route::post('/v4/comment/update', 'CommentController@update');
    Route::post('/v4/comment/destroy', 'CommentController@destroy');
    //评论
    Route::post('/v4/reply/store', 'ReplyController@store');
    Route::post('/v4/reply/update', 'ReplyController@update');
    Route::post('/v4/reply/destroy', 'ReplyController@destroy');

    //百科
    Route::get('/v4/wiki/index', 'WikiController@index');
    Route::get('/v4/wiki/category', 'WikiController@category');
    Route::get('/v4/wiki/show', 'WikiController@show');
    Route::get('/v4/wiki/related', 'WikiController@related');

    Route::post('/v4/user/sendSms', 'UserController@sendSms');
    Route::post('/v4/user/login', 'UserController@login');

    Route::get('/v4/user/wechat', 'UserController@wechat');

//    Route::namespace('Api\V4')->group([
//        'middleware' => 'api',
//        'prefix' => 'auth'
//    ], function($router) {
//        Route::post('login', 'AuthController@login');
//    });



});

