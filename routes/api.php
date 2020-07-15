<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::group(['namespace' => 'Api\V4', 'prefix' => 'v4'], function() {

    //首页
    Route::get('index/announce', 'IndexController@announce');
    Route::get('index/banner', 'IndexController@banner');
    Route::get('index/live', 'IndexController@live');
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

    //专栏
    Route::get('column/get_column_list', 'ColumnController@getColumnList');
    Route::get('column/get_column_detail', 'ColumnController@getColumnDetail');
    Route::get('column/get_column_works', 'ColumnController@getColumnWorks');
    Route::get('column/collection', 'ColumnController@Collection');
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
    Route::post('works/subscribe', 'WorksController@subscribe');
    Route::get('works/works_category_data', 'WorksController@worksCategory');


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
    Route::get('wechat_pay/wechat_notify', 'CallbackController@WechatNotify');
    Route::get('wechat_pay/ali_notify', 'CallbackController@AliNotify');

    //下单
    Route::get('order/create_column_order', 'OrderController@createColumnOrder');
    Route::get('order/create_works_order', 'OrderController@createWorksOrder');
    Route::get('order/get_coupon', 'OrderController@getCoupon');
    //生成海报
    Route::get('create/create_poster', 'CreatePosterController@CreatePoster');

    //钱包
    Route::get('income/index', 'IncomeController@index');
    Route::get('income/profit', 'IncomeController@profit');
    Route::post('income/cash_data', 'IncomeController@cashData');
    Route::get('income/present', 'IncomeController@present');
    Route::get('income/withdrawals', 'IncomeController@withdrawals');
    Route::get('income/get_list', 'IncomeController@getList');
    Route::get('income/detail', 'IncomeController@Detail');
    Route::get('income/get_deposit', 'IncomeController@getOrderDepositHistory');
    Route::get('income/send_invoice', 'IncomeController@sendInvoice');


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
    Route::get('goods/service_description', 'MallController@mallServiceDescription');
    Route::post('goods/get_coupon', 'CouponController@getCoupon');
    Route::post('goods/collect', 'MallController@collect');
    Route::get('goods/for_your_reference', 'MallController@forYourReference');

    Route::get('address/get_data', 'AddressController@getData');
    Route::post('address/create', 'AddressController@create');
    Route::get('address/get_list', 'AddressController@getList');
    Route::put('address/status_change', 'AddressController@statusChange');
    Route::get('address/list_of_shop', 'AddressController@listOfShop');

    Route::post('shopping_cart/create', 'ShoppingCartController@create');
    Route::get('shopping_cart/get_list', 'ShoppingCartController@getList');
    Route::put('shopping_cart/status_change', 'ShoppingCartController@statusChange');

    //普通订单
    Route::post('mall/prepare_create_order', 'MallOrderController@prepareCreateOrder');
    Route::post('mall/create_order', 'MallOrderController@createOrder');

    //秒杀订单
    Route::post('mall/prepare_create_flash_sale_order', 'MallOrderController@prepareCreateFlashSaleOrder');
    Route::post('mall/create_flash_sale_order', 'MallOrderController@createFlashSaleOrder');
    Route::post('mall/flash_sale_pay_fail', 'MallOrderController@flashSalePayFail');


    //拼团订单
    Route::post('mall/prepare_create_group_buy_order', 'MallOrderController@prepareCreateGroupBuyOrder');
    Route::post('mall/create_group_buy_order', 'MallOrderController@createGroupBuyOrder');

    //订单列表
    Route::get('mall/order_list', 'MallOrderController@list');
    Route::get('mall/group_buy_order_list', 'MallOrderController@listOfGroupBuy');

    //订单详情
    Route::get('mall/order_info', 'MallOrderController@orderInfo');
    Route::get('mall/group_buy_order_info', 'MallOrderController@groupBuyOrderInfo');


    //修改订单状态
    Route::put('mall/status_change', 'MallOrderController@statusChange');

    //商品评价
    Route::get('mall/no_comment_list', 'MallOrderController@noCommentList');
    Route::get('mall/get_comment', 'MallOrderController@getComment');
    Route::get('mall/comment_issue_list', 'MallOrderController@commentIssueList');
    Route::post('mall/sub_comment', 'MallOrderController@subComment');

    //售后部分
    Route::get('after_sales/list', 'AfterSalesController@list');
    Route::get('after_sales/goods_list', 'AfterSalesController@goodsList');
    Route::post('after_sales/create_order', 'AfterSalesController@createOrder');
    Route::get('after_sales/order_info', 'AfterSalesController@orderInfo');
    Route::put('after_sales/status_change', 'AfterSalesController@statusChange');
    Route::put('after_sales/refund_post', 'AfterSalesController@refundPost');
    Route::get('after_sales/reason_list', 'AfterSalesController@reasonList');

    //物流查询
    Route::get('post/get_info', 'ExpressController@getPostInfo');
    Route::get('post/company_list', 'ExpressController@companyList');

    Route::get('coupon/list', 'CouponController@list');
    //*******************************商城部分结束*******************************

    Route::post('like', 'LikeController@like');
    Route::post('unlike', 'LikeController@unlike');

    //想法
    Route::get('comment/index', 'CommentController@index');
    Route::post('comment/store', 'CommentController@store');
    Route::post('comment/update', 'CommentController@update');
    Route::post('comment/destroy', 'CommentController@destroy');
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

    //排行榜
    Route::get('rank/works', 'RankController@works');
    Route::get('rank/wiki', 'RankController@wiki');

    //我的
    Route::get('user/index', 'UserController@index');
    Route::post('user/feedback', 'UserController@feedback');
    Route::post('user/base', 'UserController@base');
    Route::post('user/followed', 'UserController@followed');
    Route::post('user/unfollow', 'UserController@unfollow');
    Route::post('user/fan', 'UserController@fan');
    Route::post('user/follower', 'UserController@follower');

    //历史记录
    Route::get('user/history', 'UserController@history');
    Route::get('user/clear_history', 'UserController@clearHistory');
    Route::get('user/collection', 'UserController@collection');

    //通知
    Route::post('notify/fans', 'NotifyController@fans');

    Route::post('auth/sms', 'AuthController@sendSms');
    Route::post('auth/login', 'AuthController@login');

    Route::get('auth/wechat', 'AuthController@wechat');

//    Route::group(['middleware' => 'auth.jwt'], function () {
//        Route::get('user/index', 'UserController@index');
//    });
});

//后台
Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function() {
    Route::get('mall_order/list', 'MallOrderController@list');
    Route::get('mall_order/details', 'MallOrderController@details');
    Route::get('mall_order/send', 'MallOrderController@send');
});
