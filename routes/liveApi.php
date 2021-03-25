<?php
//后台
Route::group(['namespace' => 'Live\V4', 'prefix' => 'live_v4'], function () {

    Route::get('index/index', 'IndexController@index');
    Route::get('index/lives', 'IndexController@lives');

});
