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


Route::group(['namespace' =>'Api\V4' ,'prefix' =>'v4'],function() {

    //首页
    Route::get('index/announce', 'IndexController@announce');
    Route::get('index/banner', 'IndexController@banner');
    Route::get('index/live', 'IndexController@live');
    Route::get('index/column', 'IndexController@column');
    Route::get('index/works', 'IndexController@works');
    Route::get('index/wiki', 'IndexController@wiki');
    Route::get('index/book', 'IndexController@book');
    Route::get('index/goods', 'IndexController@goods');

    //专栏
    Route::get('column/get_column_list', 'ColumnController@getColumnList');
    Route::get('column/get_column_detail', 'ColumnController@getColumnDetail');
    Route::get('column/get_column_works', 'ColumnController@getColumnWorks');
    Route::get('column/collection', 'ColumnController@Collection');
    Route::get('column/get_recommend', 'ColumnController@getRecommend');
    Route::get('column/get_lecture_list', 'ColumnController@getLectureList');
    Route::get('column/get_lecture_study_list', 'ColumnController@LectureStudyList');


    //课程
    Route::get('works/get_works_detail', 'WorksController@getWorksDetail');
    Route::get('works/show', 'WorksController@show');
    Route::get('works/edit_history_time', 'WorksController@editHistoryTime');
    Route::get('works/get_works_category', 'WorksController@getWorksCategoryTeacher');
    Route::get('works/get_works_content', 'WorksController@getWorksContent');
    Route::get('works/get_works_index', 'WorksController@getWorksIndex');

    //下单
    Route::get('order/create_column_order', 'OrderController@createColumnOrder');
    Route::get('order/create_works_order', 'OrderController@createWorksOrder');
    Route::get('order/get_coupon', 'OrderController@getCoupon');


    //*******************************商城部分开始*******************************

    Route::get('goods/info', 'MallController@goodsList');
    Route::get('goods/coupon_list', 'MallController@couponList');
    Route::get('goods/comment_list', 'MallController@commentList');
    Route::get('goods/category_list', 'MallController@categoryList');
    Route::get('goods/banner_list', 'MallController@bannerList');
    Route::get('goods/home_sp_list', 'MallController@homeSpList');
    Route::get('goods/flash_sale', 'MallController@flashSaleList');
    Route::get('goods/group_buy', 'MallController@groupBuyList');
    Route::get('goods/service_description', 'MallController@mallServiceDescription');
    Route::post('goods/get_coupon', 'CouponController@getCoupon');

    Route::get('address/get_data', 'AddressController@getData');
    Route::post('address/create', 'AddressController@create');
    Route::get('address/get_list', 'AddressController@getList');
    Route::put('address/status_change', 'AddressController@statusChange');
    Route::get('address/list_of_shop', 'AddressController@listOfShop');

    Route::post('shopping_cart/create', 'ShoppingCartController@create');
    Route::get('shopping_cart/get_list', 'ShoppingCartController@getList');
    Route::put('shopping_cart/status_change', 'ShoppingCartController@statusChange');

    Route::post('mall/prepare_create_order', 'MallOrderController@prepareCreateOrder');
    Route::post('mall/create_order', 'MallOrderController@createOrder');

    //*******************************商城部分结束*******************************

    //想法
    Route::get('comment/index', 'CommentController@index');
    Route::post('comment/store', 'CommentController@store');
    Route::post('comment/update', 'CommentController@update');
    Route::post('comment/destroy', 'CommentController@destroy');
    //评论
    Route::post('reply/store', 'ReplyController@store');
    Route::post('reply/update', 'ReplyController@update');
    Route::post('reply/destroy', 'ReplyController@destroy');

    //百科
    Route::get('wiki/index', 'WikiController@index');
    Route::get('wiki/category', 'WikiController@category');
    Route::get('wiki/show', 'WikiController@show');
    Route::get('wiki/related', 'WikiController@related');

    Route::post('user/sendSms', 'UserController@sendSms');
    Route::post('user/login', 'UserController@login');

    Route::get('user/wechat', 'UserController@wechat');

    //我的
    Route::get('user/index', 'UserController@index');

});


