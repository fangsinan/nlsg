<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {
    //商城订单
    Route::get('mall_order/list', 'MallOrderController@list');
    Route::post('mall_order/send', 'MallOrderController@send');

    //售后
    Route::get('after_sales/list', 'AfterSalesController@list');
    Route::post('after_sales/status_change', 'AfterSalesController@statusChange');

    //商品管理
    Route::post('goods/add', 'GoodsController@add');
    Route::get('goods/list', 'GoodsController@list');
    Route::get('goods/category_list', 'GoodsController@categoryList');

    //活动管理
    Route::post('active/add', 'ActiveController@add');
    Route::get('active/list', 'ActiveController@list');
    Route::post('active/binding', 'ActiveController@binding');
    Route::put('active/status_change', 'ActiveController@statusChange');

    //特价管理
    Route::post('special_price/add', 'SpecialPriceController@add');
    Route::get('special_price/list', 'SpecialPriceController@list');
    Route::put('special_price/status_change', 'SpecialPriceController@statusChange');

    //定时任务
    Route::get('crontab/mall_refund', 'CrontabController@mallRefund');//商城退款
    Route::get('crontab/mall_refund_check', 'CrontabController@mallRefundCheck');//商城退款查询
});
