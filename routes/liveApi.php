<?php
//后台
Route::group(['namespace' => 'Live\V4', 'prefix' => 'live_v4'], function () {

//    Route::group(['middleware' => ['auth.jwt']], function () {
    Route::group(['middleware' => ['auth.backend.jwt']], function () {
        Route::get('index/statistics', 'IndexController@index');
        Route::get('index/statistics_img_data', 'IndexController@statistics_img_data');
        Route::get('index/data', 'IndexController@data');
        Route::get('index/check_helper', 'IndexController@checkHelper');
        Route::get('index/live_users', 'IndexController@getLiveUsers');
        Route::get('index/lives', 'IndexController@lives');
        Route::post('index/create', 'IndexController@create');
        Route::post('live/delete', 'IndexController@delete');
        Route::get('live/info', 'IndexController@info');
        Route::get('comment/index', 'CommentController@index');
        Route::get('comment/show', 'CommentController@show');
        Route::post('comment/delete', 'CommentController@delete');
        Route::get('sub/index', 'SubscribeController@index');
        Route::get('order/list', 'OrderController@list');
        Route::get('order/inviter_list', 'OrderController@inviterLiveList');
    });

});
