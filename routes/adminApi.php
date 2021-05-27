<?php

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {

    Route::get('auth/captcha', 'AuthController@captcha');
    Route::post('auth/login', 'AuthController@login');
//    Route::get('mall_order/list_new', 'MallOrderController@listNew');

    Route::group(['middleware' => ['auth.backend.jwt']], function () {
        Route::post('auth/change_pwd', 'AuthController@changePassword');

        //创业天下后台列表和排序
        Route::get('channel_works/list', 'ChannelController@list');
        Route::post('channel_works/rank', 'ChannelController@rank');

        //活动管理
        Route::post('active/add', 'ActiveCont3` roller@add');
        Route::get('active/list', 'ActiveController@list');
        Route::post('active/binding', 'ActiveController@binding');
        Route::put('active/status_change', 'ActiveController@statusChange');

        Route::get('class/column', 'ClassController@column');
        Route::get('class/camp', 'ClassController@camp');
        Route::get('class/get-column-list', 'ClassController@getColumnList');
        Route::get('class/get-camp-list', 'ClassController@getCampList');
        Route::get('class/get-column-work-list', 'ClassController@getColumnWorkList');
        Route::get('class/get-lecture-work-list', 'ClassController@getLectureWorkList');
        Route::get('class/get-work-chapter-list', 'ClassController@getWorkChapterList');
        Route::get('class/lecture', 'ClassController@lecture');
        Route::get('class/works', 'ClassController@works');
        Route::get('class/listen', 'ClassController@listen');
        Route::get('class/get-work-list', 'ClassController@getWorkList');
        Route::get('class/get-chapter-info', 'ClassController@getChapterInfo');


        Route::post('class/add-column', 'ClassController@addColumn');
        Route::post('class/add-camp', 'ClassController@addCamp');
        Route::get('class/get-column-author', 'ClassController@getColumnAuthors');
        Route::post('class/add-lecture', 'ClassController@addLecture');
        Route::post('class/add-works', 'ClassController@addWorks');
        Route::post('class/add-listen', 'ClassController@addListen');
        Route::post('class/add-works-chapter', 'ClassController@addWorkChapter');
        Route::post('column/delete', 'ClassController@delColumn');
        Route::post('works/delete', 'ClassController@delWorks');
        Route::post('chapter/delete', 'ClassController@delChapter');
        Route::post('operate/chapter', 'ClassController@operateChapter');
        Route::get('works/category', 'ClassController@getWorksCategory');
        Route::get('search/category', 'ClassController@getSearchWorkCategory');
        Route::get('class/wiki', 'ClassController@wiki');
        Route::get('wiki/category', 'ClassController@getWikiCategory');
        Route::post('wiki/add', 'ClassController@addWiki');
        Route::get('wiki/edit', 'ClassController@editWiki');


        //广告
        Route::get('banner/list', 'BannerController@list');
        Route::post('banner/add', 'BannerController@add');
        Route::get('banner/edit', 'BannerController@edit');

        Route::get('index/works', 'IndexController@works');
        Route::post('index/add-works', 'IndexController@addWorks');
        Route::get('index/edit-works', 'IndexController@editWorks');
        Route::get('index/rank', 'IndexController@rank');

        Route::get('index/lists', 'IndexController@lists');
        Route::post('index/add-lists', 'IndexController@addLists');
        Route::post('index/add-listwork', 'IndexController@addListWork');
        Route::get('index/edit-lists', 'IndexController@editLists');
        Route::get('index/edit-list-work', 'IndexController@editListWork');
        Route::get('list/works', 'IndexController@getListWorks');
        Route::get('index/goods', 'IndexController@goods');
        Route::get('index/get-goods', 'IndexController@getMallGoods');
        Route::get('index/get-works', 'IndexController@getWorks');
        Route::get('index/get-rank-works', 'IndexController@getRankWorks');

        Route::get('index/get-lecture', 'IndexController@getLecture');
        Route::get('index/get-listen', 'IndexController@getListen');

        Route::post('index/add-goods', 'IndexController@addGoods');
        Route::get('index/wiki', 'IndexController@wiki');
        Route::get('index/course', 'IndexController@course');
        Route::get('index/live', 'IndexController@live');
        Route::get('index/get-lives', 'IndexController@getLives');
        Route::get('index/get-wiki', 'IndexController@getWiki');
        Route::get('index/get-offline', 'IndexController@getOfflineProduct');
        Route::post('index/add-wiki', 'IndexController@addWiki');
        Route::post('index/add-live', 'IndexController@addLive');
        Route::post('index/delete-live', 'IndexController@delLive');

        //商城订单
        Route::post('mall_order/send', 'MallOrderController@send');
        Route::post('mall_order/make_group_success', 'MallOrderController@makeGroupSuccess');
        Route::get('mall_order/tos', 'MallOrderController@tos');

        Route::get('mall_order/list', 'MallOrderController@list');

        //售后
        Route::get('after_sales/list', 'AfterSalesController@list');
        Route::post('after_sales/status_change', 'AfterSalesController@statusChange');
        Route::get('after_sales/address_list', 'AfterSalesController@addressList');

        //商品管理
        Route::post('goods/add', 'GoodsController@add');
        Route::get('goods/list', 'GoodsController@list');
        Route::get('goods/category_list', 'GoodsController@categoryList');
        Route::put('goods/change_status', 'GoodsController@changeStatus');
        Route::put('goods/change_stock', 'GoodsController@changeStock');

        //商品评论
        Route::post('goods/add_robot_comment', 'MallCommentController@addRobotComment');
        Route::get('goods/comment_list', 'MallCommentController@commentList');
        Route::post('goods/comment_reply', 'MallCommentController@replyComment');
        Route::put('goods/comment_status', 'MallCommentController@changeStatus');


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

        //配置管理
        Route::get('config/mall_keywords', 'ConfigController@mallKeywords');
        Route::post('config/edit_mall_keywords', 'ConfigController@editMallKeywords');

        //虚拟订单
        Route::get('order/list', 'OrderController@list');
        Route::get('order/statistic', 'OrderController@getOrderStatistic');
        Route::get('order/detail', 'OrderController@getOrderDetail');
        Route::get('order/user', 'OrderController@user');
        Route::get('order/lecture', 'OrderController@lecture');
        Route::get('order/reward', 'OrderController@reward');
        Route::get('order/vip', 'OrderController@vip');

        //360模块
        Route::get('vip/list', 'VipController@list');
        Route::post('vip/assign', 'VipController@assign');

        //直播
        Route::get('live/index', 'LiveController@index');
        Route::post('live/pass', 'LiveController@pass');
        Route::get('live/push', 'LiveController@push');
        Route::get('live/push', 'LiveController@push');
        Route::post('live/create', 'LiveController@create');
        Route::post('live/begin', 'LiveController@begin');
        Route::get('live/live_url_edit', 'LiveController@livePushUrlCreate');

        //用户
        Route::get('user/index', 'UserController@index');
        Route::get('user/intro', 'UserController@intro');
        Route::get('user/apply', 'UserController@apply');
        Route::post('user/pass', 'UserController@pass');

        //课程兑换码
        Route::post('redeem_code/create', 'RedeemCodeController@create');

        //评论
        Route::get('comment/index', 'CommentController@index');
        Route::post('comment/reply', 'CommentController@reply');
        Route::post('comment/forbid', 'CommentController@forbid');

        //后台角色和绑定
        Route::get('role/node_list', 'RoleController@nodeList');
        Route::post('role/node_list_create', 'RoleController@nodeListCreate');
        Route::put('role/node_list_status', 'RoleController@nodeListStatus');
        Route::post('role/role_node_bind', 'RoleController@roleNodeBind');
        Route::get('admin_user/list', 'RoleController@adminList');
        Route::put('admin_user/list_status', 'RoleController@adminListStatus');
        Route::post('admin_user/admin_create', 'RoleController@adminCreate');
        Route::get('role/role_list', 'RoleController@roleList');
        Route::get('role/role_select_list', 'RoleController@roleSelectList');
        Route::post('role/create', 'RoleController@roleCreate');

    });

    Route::post('vip/create_vip', 'VipController@createVip');

    //定时任务
    Route::get('crontab/mall_refund', 'CrontabController@mallRefund');//商城退款
    Route::get('crontab/mall_refund_check', 'CrontabController@mallRefundCheck');//商城退款查询



    //数据迁移
    Route::get('remove_data/goods', 'RemoveDataController@goods');
    Route::get('remove_data/mall_orders', 'RemoveDataController@mallOrders');
    Route::get('remove_data/a_e', 'RemoveDataController@addressExpress');
    Route::get('remove_data/vip', 'RemoveDataController@vip');
    Route::get('remove_data/redeem_code', 'RemoveDataController@redeemCode');

    //测试临时用,之后要删除
//    Route::get('live/pass', 'GoodsController@tempTools');//商城退款

    Route::post('upload/file', 'UploadController@file');//上传视频/音频
});
