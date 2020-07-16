<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function() {
    Route::get('mall_order/list', 'MallOrderController@list');
    Route::get('mall_order/details', 'MallOrderController@details');
    Route::get('mall_order/send', 'MallOrderController@send');
});
