<?php
//后台
Route::group(['namespace' => 'Live\V4', 'prefix' => 'live_v4'], function () {

//    Route::group(['middleware' => ['auth.jwt']], function () {
    Route::group(['middleware' => ['auth.backend.jwt']], function () {
        Route::get('live_index/statistics', 'IndexController@index');
        Route::get('live_index/statistics_img_data', 'IndexController@statistics_img_data');
        Route::get('live_index/data', 'IndexController@data');
        Route::get('live_index/check_helper', 'IndexController@checkHelper');
        Route::get('live_index/live_users', 'IndexController@getLiveUsers');
        Route::get('live_index/lives', 'IndexController@lives');
        Route::post('live_index/create', 'IndexController@create');
        Route::post('live_live/delete', 'IndexController@delete');
        Route::get('live_live/info', 'IndexController@info');
        Route::get('live_comment/index', 'CommentController@index');
        Route::get('live_comment/show', 'CommentController@show');
        Route::post('live_comment/delete', 'CommentController@delete');
        Route::get('live_sub/index', 'SubscribeController@index');
        Route::get('live_order/list', 'OrderController@list');
        Route::get('live_order/inviter_list', 'OrderController@inviterLiveList');

    });
    //导出
    Route::get('live_order/list_excel', 'OrderController@listExcel');
    Route::get('live_order/inviter_list_excel', 'OrderController@inviterLiveListExcel');
});
