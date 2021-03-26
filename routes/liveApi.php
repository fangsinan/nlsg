<?php
//后台
Route::group(['namespace' => 'Live\V4', 'prefix' => 'live_v4'], function () {

    Route::get('index/statistics', 'IndexController@index');
    Route::get('index/lives', 'IndexController@lives');
    Route::get('comment/index', 'CommentController@index');
    Route::get('sub/index', 'SubscribeController@index');

});
