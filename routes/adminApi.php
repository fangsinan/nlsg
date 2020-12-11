<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {

    Route::get('auth/captcha', 'AuthController@captcha');
    Route::post('auth/login', 'AuthController@login');

    Route::group(['middleware' => ['auth.backend.jwt']], function () {
        //活动管理
        Route::post('active/add', 'ActiveCont3` roller@add');
        Route::get('active/list', 'ActiveController@list');
        Route::post('active/binding', 'ActiveController@binding');
        Route::put('active/status_change', 'ActiveController@statusChange');


    });


    Route::get('class/column', 'ClassController@column');
    Route::get('class/get-column-list', 'ClassController@getColumnList');
    Route::get('class/get-column-work-list', 'ClassController@getColumnWorkList');
    Route::get('class/get-lecture-work-list', 'ClassController@getLectureWorkList');
    Route::get('class/get-work-chapter-list', 'ClassController@getWorkChapterList');
    Route::get('class/lecture', 'ClassController@lecture');
    Route::get('class/works', 'ClassController@works');
    Route::get('class/listen', 'ClassController@listen');
    Route::get('class/get-work-list', 'ClassController@getWorkList');
    Route::get('class/get-chapter-info', 'ClassController@getChapterInfo');


    Route::post('class/add-column','ClassController@addColumn');
    Route::get('class/get-column-author','ClassController@getColumnAuthors');
    Route::post('class/add-lecture','ClassController@addLecture');
    Route::post('class/add-works','ClassController@addWorks');
    Route::post('class/add-listen','ClassController@addListen');
    Route::post('class/add-works-chapter','ClassController@addWorkChapter');
    Route::post('column/delete','ClassController@delColumn');
    Route::post('works/delete','ClassController@delWorks');
    Route::post('chapter/delete','ClassController@delChapter');
    Route::post('operate/chapter','ClassController@operateChapter');
    Route::get('works/category', 'ClassController@getWorksCategory');
    Route::get('search/category', 'ClassController@getSearchWorkCategory');
    Route::get('class/wiki', 'ClassController@wiki');
    Route::get('wiki/category', 'ClassController@getWikiCategory');
    Route::post('wiki/add','ClassController@addWiki');
    Route::get('wiki/edit','ClassController@editWiki');

    //广告
    Route::get('banner/list', 'BannerController@list');
    Route::post('banner/add','BannerController@add');
    Route::get('banner/edit','BannerController@edit');

    Route::get('index/works','IndexController@works');

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
    Route::get('remove_data/goods', 'RemoveDataController@goods');
    Route::get('remove_data/mall_orders', 'RemoveDataController@mallOrders');

    //测试临时用,之后要删除
    Route::get('live/pass', 'GoodsController@tempTools');//商城退款
});
