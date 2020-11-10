<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {

    Route::get('class/column', 'ClassController@column');
    Route::get('class/lecture', 'ClassController@lecture');
    Route::get('class/works', 'ClassController@works');
    Route::get('class/listen', 'ClassController@listen');
    //商城订单
    Route::get('mall_order/list', 'MallOrderController@list');
    Route::post('mall_order/send', 'MallOrderController@send');
    Route::get('mall_order/tos', 'MallOrderController@tos');

    //售后
    Route::get('after_sales/list', 'AfterSalesController@list');
    Route::post('after_sales/status_change', 'AfterSalesController@statusChange');

    //商品管理
    Route::post('goods/add', 'GoodsController@add');
    Route::get('goods/list', 'GoodsController@list');
    Route::get('goods/category_list', 'GoodsController@categoryList');

    //活动管理
    Route::post('active/add', 'ActiveCont3` roller@add');
    Route::get('active/list', 'ActiveController@list');
    Route::post('active/binding', 'ActiveController@binding');
    Route::put('active/status_change', 'ActiveController@statusChange');

    //特价管理
    Route::post('special_price/add_normal', 'SpecialPriceController@addNormal');
    Route::post('special_price/add_flash_sale', 'SpecialPriceController@addFlashSale');
    Route::post('special_price/add_group_buy', 'SpecialPriceController@addGroupBuy');
    Route::get('special_price/list', 'SpecialPriceController@list');
    Route::get('special_price/flash_sale_list', 'SpecialPriceController@flashSaleList');
    Route::put('special_price/status_change', 'SpecialPriceController@statusChange');

    //运费模板
    Route::get('freight/list', 'FreightController@list');
    Route::get('freight/shop_list', 'FreightController@shopList');
    Route::post('freight/add_shop', 'FreightController@addShop');
    Route::get('freight/add', 'FreightController@add');


    //定时任务
    Route::get('crontab/mall_refund', 'CrontabController@mallRefund');//商城退款
    Route::get('crontab/mall_refund_check', 'CrontabController@mallRefundCheck');//商城退款查询

    //数据迁移
    Route::get('remove_data/goods', 'RemoveDataController@goods');//商城退款

    //测试临时用,之后要删除
    Route::get('live/pass', 'GoodsController@tempTools');//商城退款
});
