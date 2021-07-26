<?php

use Illuminate\Support\Facades\Route;

include __DIR__ . '/adminApi.php';
include __DIR__ . '/liveApi.php';
/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */


Route::group(['namespace' => 'Api\V4', 'prefix' => 'v4'], function () {

    //首页
    Route::get('index/announce', 'IndexController@announce');
    Route::get('index/banner', 'IndexController@banner');
    Route::get('index/live', 'IndexController@live');
    Route::get('index/lives', 'IndexController@lives');
    Route::get('index/column', 'IndexController@column');
    Route::get('index/works', 'IndexController@works');
    Route::get('index/wiki', 'IndexController@wiki');
    Route::get('index/book', 'IndexController@book');
    Route::get('index/goods', 'IndexController@goods');
    Route::get('index/course', 'IndexController@course');
    Route::get('index/rank', 'IndexController@rank');
    Route::get('index/recommend', 'IndexController@recommend');
    Route::get('index/free', 'IndexController@free');
    Route::get('index/editor', 'IndexController@editor');
    Route::get('index/version', 'IndexController@version');
    Route::get('index/event', 'IndexController@event');
    Route::get('index/market', 'IndexController@market');
    Route::post('index/share', 'IndexController@share');
    Route::get('index/test', 'IndexController@test');
    Route::get('jpush/remove_alias', 'IndexController@jpushAlias');
    Route::get('config', 'IndexController@config');
    Route::get('temp_config', 'IndexController@tempConfig');
    Route::get('index/camp', 'IndexController@camp');

    //统计
    Route::get('index/Kunsaid', 'IndexController@kunSaid');

    //专栏
    Route::get('column/get_column_list', 'ColumnController@getColumnList');
    Route::get('column/get_column_detail', 'ColumnController@getColumnDetail');
    Route::get('column/get_column_works', 'ColumnController@getColumnWorks');
    Route::get('column/get_recommend', 'ColumnController@getRecommend');
    Route::get('column/get_lecture_list', 'ColumnController@getLectureList');
    Route::get('column/get_lecture_study_list', 'ColumnController@LectureStudyList');


    //课程
    Route::get('works/get_works_detail', 'WorksController@getWorksDetail');
    Route::get('works/show', 'WorksController@show');
    Route::get('works/edit_history_time', 'WorksController@editHistoryTime');
    Route::get('works/get_works_category', 'WorksController@getWorksCategoryTeacher');
    Route::get('works/get_works_content', 'WorksController@getWorksContent');
    Route::get('works/get_works_index', 'WorksController@getWorksIndex');
    Route::get('works/works_category_data', 'WorksController@worksCategory');
    Route::get('works/materials', 'WorksController@materials');


    //听书
    Route::get('book/get_book_list', 'ListenBookController@getBookList');
    Route::get('book/get_book_list_detail', 'ListenBookController@getBookListDetail');
    Route::get('book/get_new_book_list', 'ListenBookController@getNewBookList');
    Route::get('book/get_book_index', 'ListenBookController@ListenBookIndex');
    Route::get('book/get_listen_detail', 'ListenBookController@getListenDetail');

    //搜索
    Route::get('search/index', 'SearchController@index');
    Route::get('search/search', 'SearchController@search');
    //微信支付
    Route::get('pay/wechat_pay', 'PayController@prePay');
    //支付宝支付
    Route::get('pay/ali_pay', 'PayController@aliPay');
    //支付是否成功查询
    Route::get('pay/order_find', 'PayController@OrderFind');
    //苹果支付
    Route::get('pay/apple_pay', 'PayController@ApplePay');
    Route::get('pay/pay_coin', 'PayController@PayCoin');


    //微信回调
    Route::post('wechat_pay/wechat_notify', 'CallbackController@WechatNotify');
    Route::post('wechat_pay/wechat_jsapi_notify', 'CallbackController@WechatNotifyJsapi');
    Route::post('wechat_pay/ali_notify', 'CallbackController@AliNotify');


    //生成海报
    Route::get('create/create_poster', 'CreatePosterController@CreatePoster');
    Route::post('create/upload_push', 'CreatePosterController@uploadPush');
    //上传阿里点播、OSS
    Route::post('upload/push_ali_auth', 'AliUploadController@PushAliAuth');
    Route::post('upload/del_ali_ydb', 'AliUploadController@DelAliYdb');
    Route::post('upload/get_play', 'AliUploadController@GetPlay');
    Route::post('upload/file_ali_oss', 'AliUploadController@FileAliOss');
    Route::post('upload/del_ali_oss', 'AliUploadController@DelAliOss');
    Route::post('upload/callback', 'AliUploadController@Callback');


    //*******************************商城部分开始*******************************
    Route::get('goods/info', 'MallController@goodsList');
    Route::get('goods/coupon_list', 'MallController@couponList');
    Route::get('goods/comment_list', 'MallController@commentList');
    Route::get('goods/category_list', 'MallController@categoryList');
    Route::get('goods/banner_list', 'MallController@bannerList');
    Route::get('goods/home_sp_list', 'MallController@homeSpList');
    Route::get('goods/flash_sale', 'MallController@flashSaleList');
    Route::get('goods/group_buy', 'MallController@groupBuyList');
    Route::get('goods/group_buy_info', 'MallController@groupByGoodsInfo');
    Route::get('goods/group_buy_team_list', 'MallOrderController@groupByTeamList');
    Route::get('goods/group_buy_scrollbar', 'MallOrderController@gbScrollbar');
    Route::get('goods/service_description', 'MallController@mallServiceDescription');
    Route::get('goods/buyer_reading', 'MallController@buyerReading');
    Route::get('goods/buyer_reading_gb', 'MallController@buyerReadingForGroupBuy');
    Route::get('goods/for_your_reference', 'MallController@forYourReference');
    Route::get('address/list_of_shop', 'AddressController@listOfShop');
    Route::get('shopping_cart/get_count', 'ShoppingCartController@getCount');
    Route::get('mall_coupon/rule', 'MallController@getCouponList');
    Route::get('mall/comment_issue_list', 'MallOrderController@commentIssueList');
    Route::get('after_sales/reason_list', 'AfterSalesController@reasonList');
    Route::get('coupon/list', 'CouponController@list');
    Route::post('coupon/give', 'CouponController@giveCoupon');
    Route::get('post/company_list', 'ExpressController@companyList');//快递公司列表
    //*******************************商城部分结束*******************************

    Route::get('vip/home_page', 'VipController@homePage');
    Route::get('vip/explain', 'VipController@explain');
    Route::get('vip/all_works', 'VipController@allWorks');

    //创业天下
    Route::post('channel/click', 'ChannelController@click');
    Route::post('channel/login', 'ChannelController@login');
    Route::get('channel/banner', 'ChannelController@cytxBanner');
    //想法
    Route::get('comment/list', 'CommentController@index');

    Route::get('comment/show', 'CommentController@show');
    Route::get('comment/forward/user', 'CommentController@getForwardUser');
    Route::get('comment/like/user', 'CommentController@getLikeUser');

    //评论
    Route::post('reply/store', 'ReplyController@store');
    Route::post('reply/update', 'ReplyController@update');
    Route::post('reply/destroy', 'ReplyController@destroy');

    //百科
    Route::get('wiki/index', 'WikiController@index');
    Route::get('wiki/category', 'WikiController@category');
    Route::get('wiki/show', 'WikiController@show');
    Route::get('wiki/related', 'WikiController@related');
    Route::post('wiki/update-views', 'WikiController@updateWikiView');


    //排行榜
    Route::get('rank/works', 'RankController@works');
    Route::get('rank/wiki', 'RankController@wiki');
    Route::get('rank/goods', 'RankController@goods');

    //我的
    Route::get('user/homepage', 'UserController@homepage');
    Route::get('user/feed', 'UserController@feed');
    Route::post('user/feedback', 'UserController@feedback');
    Route::get('user/base', 'UserController@base');
    Route::get('user/invitation_record', 'UserController@invitationRecord');//邀请记录


    Route::post('auth/check_wx', 'AuthController@checkWx');
    Route::post('auth/bind', 'AuthController@bind');
    Route::post('auth/channel_bind', 'AuthController@channel_bind');
    Route::post('auth/sub_phone', 'AuthController@sub_phone');
    Route::post('user/check_phone', 'UserController@checkPhone');

    //历史记录
    Route::get('user/history', 'UserController@history');
    Route::get('user/new_history', 'UserController@new_history');
    Route::get('user/clear_history', 'UserController@clearHistory');
    Route::get('user/collection', 'UserController@collection');

    //通知
    Route::post('notify/fans', 'NotifyController@fans');

    Route::post('auth/sms', 'AuthController@sendSms');
    Route::post('auth/login', 'AuthController@login');

    Route::post('auth/wechat', 'AuthController@wechat');
    Route::post('auth/wechat_info', 'AuthController@wechatInfo');
    Route::get('auth/switch', 'AuthController@switch');
    Route::post('auth/apple', 'AuthController@apple');
    Route::post('bind/apple', 'AuthController@jwtApple');
    Route::get('auth/check_phone', 'AuthController@checkPhone');
    Route::get('auth/module', 'AuthController@module');

    Route::get('order/reward/user', 'OrderController@getRewardUser');

    Route::get('live/index', 'LiveController@index');
    Route::get('live/lists', 'LiveController@getLiveLists');
    Route::get('live/recommend', 'LiveController@recommend');
    Route::get('live/back_lists', 'LiveController@getLiveBackLists');
    Route::get('live/check_sub', 'LiveController@checkLiveSub');
    Route::get('live/team_info', 'LiveController@liveTeam');


    Route::get('check_phone_add_sub', 'LiveController@checkPhoneAddSub');


    Route::post('send/get_send_order', 'SendController@getSendOrder');

    Route::get('work/convert', 'WorksController@convert');//获取订单详情

    Route::group(['middleware' => ['auth.jwt']], function () {
        Route::get('user/coupon', 'UserController@getUserCoupon');
        Route::get('user/base', 'UserController@base');
        Route::get('user/account', 'UserController@account');
        Route::post('user/store', 'UserController@store');
        Route::post('user/followed', 'UserController@followed');
        Route::post('user/unfollow', 'UserController@unfollow');
        Route::get('user/statistics', 'UserController@statistics');
        Route::get('user/fan', 'UserController@fan');
        Route::get('user/follower', 'UserController@follower');
        Route::get('user/invitation_record', 'UserController@invitationRecord');
        Route::post('change/phone', 'UserController@changePhone');
        Route::post('bind/wechat', 'UserController@bindWechat');
        Route::post('remove/wechat', 'UserController@removeWechat');
        Route::get('user/edit_user', 'UserController@editUserInfo');
        //切歌
        Route::get('works/neighbor', 'WorksController@neighbor');

        //会场销售
        Route::get('meeting_sales/index', 'MeetingController@salesIndex');
        Route::get('meeting_sales/check_dealer', 'MeetingController@checkDealer');
        Route::post('meeting_sales/bind_dealer', 'MeetingController@bindDealer');
        Route::get('meeting_sales/bind_record', 'MeetingController@bindDealerRecord');

        //商城开始
        Route::post('shopping_cart/create', 'ShoppingCartController@create');//添加购物车
        Route::get('shopping_cart/get_list', 'ShoppingCartController@getList');//购物车列表
        Route::put('shopping_cart/status_change', 'ShoppingCartController@statusChange');//购物车状态修改

        Route::get('address/get_data', 'AddressController@getData');//收货地址详情
        Route::post('address/create', 'AddressController@create');//创建收获地址
        Route::get('address/get_list', 'AddressController@getList');//收货地址列表
        Route::put('address/status_change', 'AddressController@statusChange');//收货地址状态修改

        Route::get('after_sales/list', 'AfterSalesController@list');//售后列表
        Route::get('after_sales/goods_list', 'AfterSalesController@goodsList');//可售后商品列表
        Route::post('after_sales/create_order', 'AfterSalesController@createOrder');//创建售后单
        Route::get('after_sales/order_info', 'AfterSalesController@orderInfo');//售后单详情
        Route::put('after_sales/status_change', 'AfterSalesController@statusChange');//售后状态修改
        Route::put('after_sales/refund_post', 'AfterSalesController@refundPost');//售后快递信息提交

        Route::post('goods/get_coupon', 'CouponController@getCoupon');//领取商品优惠券
        Route::get('post/get_info', 'ExpressController@getPostInfo');//快递信息

        Route::post('goods/collect', 'MallController@collect');//商品收藏
        Route::post('goods/sub', 'MallController@sub');//商品补货提醒
        Route::post('home/redeem_code', 'MallController@redeemCode');//兑换码
        Route::get('home/redeem_code_list', 'MallController@redeemCodeList');//兑换码

        Route::post('mall/prepare_create_order', 'MallOrderController@prepareCreateOrder');//普通预下单
        Route::post('mall/create_order', 'MallOrderController@createOrder');//普通下单
        Route::post('mall/prepare_create_flash_sale_order', 'MallOrderController@prepareCreateFlashSaleOrder');//秒杀预下单
        Route::post('mall/create_flash_sale_order', 'MallOrderController@createFlashSaleOrder');//秒杀下单
        Route::post('mall/flash_sale_pay_fail', 'MallOrderController@flashSalePayFail');//秒杀支付失败删除订单
        Route::post('mall/prepare_create_group_buy_order', 'MallOrderController@prepareCreateGroupBuyOrder');//拼团预下单
        Route::post('mall/create_group_buy_order', 'MallOrderController@createGroupBuyOrder');//拼团下单

        Route::get('mall/order_list', 'MallOrderController@list');//普通和秒杀订单列表
        Route::get('mall/comment_list', 'MallOrderController@commentList');//商品评价列表
        Route::get('mall/order_info', 'MallOrderController@orderInfo');//普通和秒杀订单详情
        Route::get('mall/group_buy_order_list', 'MallOrderController@listOfGroupBuy');//拼团列表
        Route::get('mall/group_buy_order_info', 'MallOrderController@groupBuyOrderInfo');//拼团订单详情
        Route::put('mall/status_change', 'MallOrderController@statusChange');//订单状态修改
        Route::post('mall/sub_comment', 'MallOrderController@subComment');//评论
        Route::get('mall/get_comment', 'MallOrderController@getComment');//获取评论内容
        //商城结束

        //创业天下
        Route::get('channel/cytx', 'ChannelController@cytx');
        Route::get('channel/cytx_new', 'ChannelController@cytxNew');
        Route::get('channel/cytx_order', 'ChannelController@cytxOrder');

        //*******************************新会员部分*******************************

        Route::get('vip/code_list', 'VipController@redeemCodeList');
        Route::put('vip/code_send', 'VipController@redeemCodeSend');
        Route::put('vip/code_take_back', 'VipController@redeemCodeTakeBack');
        Route::put('vip/code_use', 'VipController@redeemCodeUse');
        Route::post('vip/code_create', 'VipController@redeemCodeCreate');
        Route::post('vip/code_get', 'VipController@redeemCodeGet');
        Route::get('vip/code_info', 'VipController@redeemCodeInfo');

        //*******************************我的直播部分开始*******************************
        Route::post('live_console/add', 'LiveConsoleController@add');
        Route::post('live_console/check_helper', 'LiveConsoleController@checkHelper');
        Route::put('live_console/change_status', 'LiveConsoleController@changeStatus');
        Route::get('live_console/list', 'LiveConsoleController@list');
        Route::get('live_console/info', 'LiveConsoleController@info');
        //*******************************直播画面页*******************************
        Route::put('live_console/change_info_status', 'LiveConsoleController@changeInfoState');//开始停止直播

        Route::post('live_console/push_msg_to_live', 'LiveConsoleController@pushMsgToLive');//推送商品
        Route::get('live_console/push_msg_list', 'LiveConsoleController@pushMsgList');//推送商品记录
        Route::put('live_console/change_push_msg_state', 'LiveConsoleController@changePushMsgState');//推送记录状态修改

        Route::post('live_notice/add', 'LiveConsoleController@createLiveNotice');
        Route::get('live_notice/list', 'LiveConsoleController@liveNoticeList');
        Route::put('live_notice/change_state', 'LiveConsoleController@changeLiveNoticeState');

        Route::post('live_forbid/add', 'LiveConsoleController@forbid');//禁言
        //*******************************我的直播部分开始*******************************


        Route::get('auth/logout', 'AuthController@logout');

        //虚拟订单  str

        //下单
        Route::post('order/create_column_order', 'OrderController@createColumnOrder');
        Route::post('order/create_works_order', 'OrderController@createWorksOrder');
        Route::post('order/create_reward_order', 'OrderController@createRewardOrder');
        Route::post('order/create_coin_order', 'OrderController@createCoinOrder');
        Route::post('order/create_new_vip_order', 'OrderController@createNewVipOrder'); //360下单
        Route::post('order/create_products_order', 'OrderController@createProductsOrder'); //线下课

        Route::get('order/get_coupon', 'OrderController@getCoupon');
        Route::get('order/order_list', 'OrderController@orderList');
        Route::get('order/order_detail', 'OrderController@orderDetail');
        Route::get('order/close_order', 'OrderController@closeOrder');

        Route::post('works/subscribe', 'WorksController@subscribe');
        Route::get('works/works_sub_works', 'WorksController@worksSubWorks');

        //创业天下下单
        Route::post('order/create_column_cytx_order', 'OrderController@createColumnCytxOrder');
        Route::post('order/create_works_cytx_order', 'OrderController@createWorksCytxOrder');

        //虚拟订单  end


        //钱包
        Route::get('income/index', 'IncomeController@index');
        Route::get('income/profit', 'IncomeController@profit');
        Route::post('income/cash_data', 'IncomeController@cashData');
        Route::get('income/present', 'IncomeController@present');
        Route::get('income/withdrawals', 'IncomeController@withdrawals');
        Route::get('income/get_withdraw', 'IncomeController@getWithdraw');
        Route::get('income/get_list', 'IncomeController@getList');
        Route::get('income/detail', 'IncomeController@Detail');
        Route::get('income/get_deposit', 'IncomeController@getOrderDepositHistory');
        Route::get('income/send_invoice', 'IncomeController@sendInvoice');
        Route::get('order/get_subscribe', 'OrderController@getSubscribe');
        Route::get('column/collection', 'ColumnController@Collection');

        //喜欢
        Route::post('like', 'LikeController@like');
        Route::post('unlike', 'LikeController@unlike');

        //想法
        Route::post('comment/store', 'CommentController@store');
        Route::post('comment/update', 'CommentController@update');
        Route::post('comment/destroy', 'CommentController@destroy');


        //直播
        Route::get('live/channels', 'LiveController@getLiveChannel');
        Route::get('live/show', 'LiveController@show');
        Route::post('live/check_password', 'LiveController@checkLivePassword');
        Route::get('offline/info', 'LiveController@getOfflineInfo');
        Route::get('offline/order', 'LiveController@getOfflineOrder');
        Route::get('live/ranking', 'LiveController@ranking');
        Route::post('live/retype', 'LiveController@reLiveType');
        Route::post('live/free_order', 'LiveController@freeLiveOrder');
        Route::post('live/pay_order', 'LiveController@payLiveOrder');
        //通知列表
        Route::get('notify/list', 'NotifyController@index');
        Route::get('notify/fans', 'NotifyController@fans');
        Route::post('notify/push', 'NotifyController@jpush');
        Route::get('notify/systerm', 'NotifyController@systerm');
        Route::get('notify/course', 'NotifyController@course');
        Route::post('notify/settings', 'NotifyController@settings');
        Route::get('user/notify_settings', 'NotifyController@getNotifySettings');

        //赠送流程
        Route::post('order/create_send_order', 'OrderController@createSendOrder'); //赠送下单
        Route::post('send/send_edit', 'SendController@getSendEdit');

        //Im
        Route::post('im/msg_collection', 'ImMsgController@MsgCollection');
        Route::post('im/msg_collection_list', 'ImMsgController@MsgCollectionList');
        Route::post('im_group/edit_join_group', 'ImGroupController@editJoinGroup');
        Route::post('im_group/forbid_send_msg', 'ImGroupController@forbidSendMsg');
        Route::post('im_group/set_group_user', 'ImGroupController@setGroupUser');


        //im文案部分
        Route::get('im_doc/list', 'ImDocController@list');
        Route::post('im_doc/add', 'ImDocController@add');
        Route::post('im_doc/add_for_app', 'ImDocController@addForApp');
        Route::put('im_doc/change_status', 'ImDocController@changeStatus');
        Route::get('im_doc/job_list', 'ImDocController@sendJobList');
        Route::get('im_doc/job_list_for_app', 'ImDocController@sendJobListForApp');
        Route::post('im_doc/job_add', 'ImDocController@addSendJob');
        Route::put('im_doc/change_job_status', 'ImDocController@changeJobStatus');
        Route::get('im_doc/group_list', 'ImDocController@groupList');

        //im选择商品
        Route::get('im_doc/category', 'ImDocController@getCategory');
        Route::get('im_doc/category/product', 'ImDocController@getCategoryProduct');

    });
    Route::post('im_group/forbid_msg_list', 'ImGroupController@forbidMsgList');

    //IM
    Route::any('callback/callbackMsg', 'CallbackController@callbackMsg');
    Route::get('im/get_user_sig', 'ImController@getUserSig');
    Route::post('im/msg_send_all', 'ImMsgController@MsgSendAll');
    Route::get('im_friend/get_im_user', 'ImFriendController@getImUser');


    Route::get('ToTwitter', 'IncomeController@ToTwitter');

});

