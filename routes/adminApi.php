<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {
    Route::get('mall_order/list', 'MallOrderController@list');
    Route::post('mall_order/send', 'MallOrderController@send');
    Route::get('after_sales/list', 'AfterSalesController@list');
    Route::post('goods/add', 'GoodsController@add');
});
