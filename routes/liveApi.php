<?php
//后台
Route::group(['namespace' => 'Live\V4', 'prefix' => 'live_v4'], function () {

    Route::get('index/statistics', 'IndexController@index');
    Route::get('index/data', 'IndexController@data');
    Route::get('index/check_helper', 'IndexController@checkHelper');
    Route::get('index/live_users', 'IndexController@getLiveUsers');
    Route::get('index/lives', 'IndexController@lives');
    Route::get('comment/index', 'CommentController@index');
    Route::get('comment/show', 'CommentController@show');
    Route::get('sub/index', 'SubscribeController@index');

});
