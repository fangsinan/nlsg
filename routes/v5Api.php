<?php



Route::group(['namespace' => 'Api\V5', 'prefix' => 'v5'], function () {


    /**   5.0 API  START   ***/

    //首页排序
    Route::any('index/index_position', 'IndexController@indexPosition');
    Route::any('index/index_middle', 'IndexController@indexMiddle');
    Route::any('index/index_works', 'IndexController@indexWorks');
    Route::any('index/get_top_img', 'IndexController@getTopImg');
    Route::any('index/flash_banner', 'IndexController@flashBanner');
    Route::any('index/lives', 'IndexController@lives');

    Route::any('column/get_camp_list', 'ColumnController@getCampList');



    Route::any('video/get_random_video', 'VideoController@getRandomVideo');
    Route::any('video/like', 'VideoController@like');

    Route::any('user/user_his_list', 'UserController@userHisList');
    Route::any('vip/new_home_page', 'VipController@newHomePage');


    //切歌
    Route::any('works/neighbor', 'WorksController@neighbor');
    Route::any('works/get_lists_works', 'WorksController@getListsWorks');

    Route::group(['middleware' => ['auth.jwt']], function () {
        Route::any('user/history_like', 'UserController@histLike');

    });

    /**     5.0 API  END    ***/
});

