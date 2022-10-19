<?php

//后台

Route::group(['namespace' => 'Admin\V4', 'prefix' => 'admin_v4'], function () {

    Route::get('deal/get_order_info', 'DealController@getOrderInfo');
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

        Route::get('class/camp_clock_in', 'ClassController@CampClockIn');
        Route::get('class/camp_clock_in_info', 'ClassController@CampClockInInfo');

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
        Route::post('goods/add_robot_comment_for_works', 'MallCommentController@addRobotCommentForWorks');
        Route::get('sub_helper/works_ojb_list', 'SubHelperController@worksObjList');
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
        Route::get('order/col_list', 'OrderController@colList');
        Route::get('order/statistic', 'OrderController@getOrderStatistic');
        Route::get('order/detail', 'OrderController@getOrderDetail');
        Route::get('order/user', 'OrderController@user');
        Route::get('order/lecture', 'OrderController@lecture');
        Route::get('order/reward', 'OrderController@reward');
        Route::get('order/vip', 'OrderController@vip');

        //360模块
        Route::get('vip/list', 'VipController@list');
        Route::post('vip/assign', 'VipController@assign');
        Route::post('vip/change_360_expire_time', 'VipController@change360ExpireTime');

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

        //自助开通
        Route::get('sub_helper/ojb_list', 'SubHelperController@objList');
        Route::post('sub_helper/open', 'SubHelperController@open');
        Route::post('sub_helper/close', 'SubHelperController@close');

        Route::get('sub_helper/com_ojb_list', 'SubHelperController@comObjList');

        //消息列表

        //im文案部分
        Route::get('im_doc/list', 'ImDocController@list');
        Route::post('im_doc/add', 'ImDocController@add');
        Route::put('im_doc/change_status', 'ImDocController@changeStatus');
        Route::any('im_doc/job_list', 'ImDocController@sendJobList');
        Route::post('im_doc/job_add', 'ImDocController@addSendJob');
        Route::put('im_doc/change_job_status', 'ImDocController@changeJobStatus');
        Route::get('im_doc/group_list', 'ImDocController@groupList');

        //im聊天记录部分
        Route::get('im_msg/list', 'ImMsgController@getMsgList');

        //im群
        Route::get('im_group/list', 'ImGroupController@list');
        Route::get('im_group/statistics', 'ImGroupController@statistics');
        Route::put('im_group/change_top', 'ImGroupController@changeTop');
        Route::post('im_group/bind_works', 'ImGroupController@bindWorks');
        Route::post('im_group/edit_join_group', 'ImGroupController@editJoinGroup');
        Route::post('im_group/create_group', 'ImGroupController@createGroup');
        Route::post('im_group/destroy_group', 'ImGroupController@destroyGroup');
        Route::post('im_group/change_group_owner', 'ImGroupController@changeGroupOwner');
        Route::post('im_group/get_group_member_info', 'ImGroupController@getGroupMemberInfo');


        //im用户
        Route::get('im_user/list', 'ImUserController@list');
        Route::get('im_user/friends_list', 'ImUserController@friendsList');
        Route::get('im_user/order_list', 'ImUserController@orderList');
        Route::get('im_user/mall_order_list', 'ImUserController@mallOrderList');
        Route::get('im_user/recharge_order', 'ImUserController@rechargeOrder');
        Route::get('im_user/history', 'ImUserController@history');

        //im快捷恢复
        Route::get('im_quick_reply/list', 'ImQuickReply@list');
        Route::post('im_quick_reply/add', 'ImQuickReply@add');
        Route::put('im_quick_reply/change_status', 'ImQuickReply@changeStatus');

        //im选择商品
        Route::get('im_doc/category', 'ImDocController@getCategory');
        Route::get('im_doc/category/product', 'ImDocController@getCategoryProduct');

        //im 好友关系
        Route::post('im_friend/friend_check', 'ImFriendController@friendCheck');
        Route::get('im_friend/portrait_get', 'ImFriendController@getPortrait');
        Route::post('im_friend/add_friend', 'ImFriendController@addFriend');
        Route::post('im_friend/del_friend', 'ImFriendController@delFriend');
        Route::post('im_friend/im_friend_list', 'ImFriendController@imFriendList');

        //im收藏
        Route::post('im/msg_collection', 'ImMsgController@MsgCollection');
        Route::post('im/msg_collection_list', 'ImMsgController@MsgCollectionList');

        //直播过滤词
        Route::get('s_key/list', 'ShieldKeyController@list');
        Route::post('s_key/add', 'ShieldKeyController@add');
        Route::put('s_key/change_status', 'ShieldKeyController@changeStatus');

        //虚拟订单批量退款
        Route::get('orl/list', 'OrderRefundLogController@list');
        Route::post('orl/add', 'OrderRefundLogController@add');

        //im文案文件夹
        Route::get('im_doc_folder/list', 'ImDocFolderController@list');
        Route::post('im_doc_folder/add', 'ImDocFolderController@add');
        Route::put('im_doc_folder/change_status', 'ImDocFolderController@changeStatus');

        Route::post('im_doc_folder/add_doc', 'ImDocFolderController@addDoc');
        Route::put('im_doc_folder/change_doc_status', 'ImDocFolderController@changeDocStatus');

        Route::get('im_doc_folder/folder_doc_List', 'ImDocFolderController@folderDocList');
        Route::get('im_doc_folder/job_list', 'ImDocFolderController@jobList');
        Route::post('im_doc_folder/add_job', 'ImDocFolderController@addJob');
        Route::put('im_doc_folder/change_job_status', 'ImDocFolderController@changeJobStatus');



        /* 5.0 */

        Route::get('video/video-list', 'VideoController@video_list');
        Route::post('video/add-video-info', 'VideoController@addVideoInfo');
        Route::get('video/del-video', 'VideoController@delVideoInfo');
        /* 5.0 */


        //后台训练营订单
        Route::get('order/campList', 'OrderController@campList');

        //微信客户管理sgl0329
        Route::get('user_wechat/test', 'UserWechatController@test');
        Route::get('user_wechat/search_wechat_user_list', 'UserWechatController@search_wechat_user_list');
        Route::get('user_wechat/search_wechat_staff_user_list', 'UserWechatController@search_wechat_staff_user_list');
        Route::get('user_wechat/get_wechat_staff_user_list', 'UserWechatController@get_wechat_staff_user_list');
        Route::get('user_wechat/transfer_customer', 'UserWechatController@transfer_customer');
        Route::get('user_wechat/transfer_result', 'UserWechatController@transfer_result');
        Route::get('user_wechat/search_transfer_record', 'UserWechatController@search_transfer_record');
        Route::get('user_wechat/search_transfer_log', 'UserWechatController@search_transfer_log');
        Route::get('user_wechat/transfer_customer_batch', 'UserWechatController@transfer_customer_batch');
        Route::get('user_wechat/get_offline_products', 'UserWechatController@get_offline_products');

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

    Route::get('im_doc/test', 'ImDocController@test');

    Route::post('upload/file', 'UploadController@file');//上传视频/音频

    Route::get('task/index','TaskController@index');
    Route::post('task/send','TaskController@send');
    Route::get('task/goods','TaskController@getGoods');
    Route::get('task/lives','TaskController@getLives');
    Route::get('task/lectures','TaskController@getLectures');
    Route::get('task/users','TaskController@getUsers');
    Route::get('task/works','TaskController@getWorks');

    Route::get('order/list_excel', 'OrderController@listExcel');
    Route::get('order/col_list_excel', 'OrderController@colListExcel');
    Route::get('order/campListExcel', 'OrderController@campListExcel');


    //打卡导出sgl0329
    Route::get('class/export_camp_clock_in_info', 'ClassController@ExportCampClockInInfo');


});



Route::group(['namespace' => 'Admin\V5', 'prefix' => 'admin_v5'], function () {
    Route::get('temp_tools/iout', 'TempToolsController@insertOnlineUserTest');
    Route::get('temp_tools/iouct', 'TempToolsController@liveOnlineUserCountsTest');
    Route::get('temp_tools/dou_dian_test', 'TempToolsController@douDianTest');

    Route::group(['middleware' => ['auth.backend.jwt']], function () {

        //推荐位
        Route::get('recommend_config/list', 'RecommendConfigController@list');
        Route::post('recommend_config/change_status', 'RecommendConfigController@changeStatus');
        Route::post('recommend_config/sort', 'RecommendConfigController@sort');
        Route::post('recommend_config/add', 'RecommendConfigController@add');
        Route::get('recommend_config/info', 'RecommendConfigController@info');
        Route::get('recommend_config/info_select_list', 'RecommendConfigController@infoSelectList');
        Route::post('recommend_config/info_bind', 'RecommendConfigController@infoBind');
        Route::post('recommend_config/del_info_bind', 'RecommendConfigController@delInfoBind');



        Route::get('select_data/recommend_type_list', 'SelectDataController@recommendTypeList');
        Route::get('select_data/works_list', 'SelectDataController@worksList');
        Route::get('select_data/works_lists_list', 'SelectDataController@worksListsList');
        Route::get('select_data/teacher_list', 'SelectDataController@teacherList');
        Route::get('select_data/live_classify', 'SelectDataController@liveClassify');
        Route::get('select_data/live_list', 'SelectDataController@liveList');
        Route::get('select_data/live_valid_time_list', 'SelectDataController@liveValidTimeList');

        Route::get('temp_tools/live_tools', 'TempToolsController@liveTools');



        Route::get('erp_order/list', 'ErpOrderController@erpOrderList');//虚拟订单需发货列表
        Route::get('erp_order/list_excel', 'ErpOrderController@erpOrderListExcel');//虚拟订单需发货列表
        Route::post('erp_order/add_refund_order', 'ErpOrderController@addRefundOrder');//虚拟订单需发货列表
        Route::post('address/add', 'AddressController@create');
        Route::post('erp_order/bind_address', 'ErpOrderController@bindAddress');
        Route::get('address/get_data', 'AddressController@getData');//收货地址详情



        Route::get('teacher/list', 'TeacherController@list');
        Route::get('teacher/info', 'TeacherController@info');
        Route::post('teacher/create', 'TeacherController@create');


        Route::get('temp_live_excel/shou_ting_qing_kuang', 'TempLiveExcelController@shouTingQingKuang');
        Route::get('temp_live_excel/wei_jin_zhi_bo', 'TempLiveExcelController@weiJinZhiBo');
        Route::get('temp_live_excel/qi_ye_wei_xin', 'TempLiveExcelController@qiYeWeiXin');
        Route::get('temp_live_excel/shou_ting_qing_kuang_free', 'TempLiveExcelController@shouTingQingKuangFree');

        Route::get('dou_dian/order_list','DouDianController@orderList');
        Route::get('dou_dian/select_goods_list','DouDianController@selectGoodsList');
        Route::get('dou_dian/select_order_status','DouDianController@selectOrderStatus');
        Route::get('dou_dian/order_decrypt_quota','DouDianController@orderDecryptQuota');
        Route::post('dou_dian/order_decrypt_quota_reset','DouDianController@orderDecryptQuotaReset');
        Route::get('dou_dian/order_list_excel','DouDianController@orderListExcel');

        Route::get('offline_order/order_list','OfflineOrderController@orderList');
        Route::get('offline_order/order_log_list','OfflineOrderController@orderLogList');
        Route::post('offline_order/order_log_add','OfflineOrderController@orderLogAdd');

        #短链接生成模块
        Route::post('short_link/link_admin_list','ShortLinkController@linkAdminList'); //短链接管理员列表
        Route::post('short_link/link_get','ShortLinkController@linkGet'); //获取短链接
        Route::post('short_link/link_list','ShortLinkController@linkList');  //短链接列表
        Route::post('short_link/link_sdd_edit','ShortLinkController@linkAddEdit'); //添加编辑短链接
        Route::post('short_link/link_show','ShortLinkController@linkShow'); //查看短链接


        //message模块
        Route::post('msg_console/job_list','MsgConsoleController@jobList');
        Route::post('msg_console/create_job','MsgConsoleController@createJob');
        Route::post('msg_console/job_status','MsgConsoleController@jobStatus');
        Route::post('msg_console/template_list','MsgConsoleController@templateList');
        Route::post('msg_console/create_template','MsgConsoleController@createTemplate');
        Route::post('msg_console/msg_type_list','MsgConsoleController@msgTypeList');
        Route::post('msg_console/template_status','MsgConsoleController@templateStatus');
        Route::post('msg_console/jpush_msg_type_list','MsgConsoleController@jPushMsgTypeList');
        Route::post('msg_console/mrt_search_data','MsgConsoleController@msgRelationTypeSearchData');


        //直播地图
        Route::post('live_bgp/list','LiveBgpController@list');


        //***********帮助留言与反馈部分***********
        Route::post('feedback/list','FeedbackController@list');
        Route::post('feedback/change_status','FeedbackController@changeStatus');
        Route::post('feedback/to_reply','FeedbackController@toReply');
        Route::post('feedback/template_list','FeedbackController@templateList');
        Route::post('feedback/template_create','FeedbackController@templateCreate');
        Route::post('feedback/template_change_status','FeedbackController@templateChangeStatus');
        Route::post('feedback/type_list','FeedbackController@typeList');
        Route::post('feedback/type_create','FeedbackController@typeCreate');
        Route::post('feedback/type_change_status','FeedbackController@typeChangeStatus');
        Route::post('feedback/help_list','FeedbackController@helpList');
        Route::post('feedback/help_create','FeedbackController@helpCreate');
        Route::post('feedback/help_change_status','FeedbackController@helpChangeStatus');


        Route::post('talk/list','TalkController@list');
        Route::post('talk/change_status','TalkController@changeStatus');
        Route::post('talk/remark_create','TalkController@remarkCreate');
        Route::post('talk/remark_list','TalkController@remarkList');
        Route::post('talk/talk_list','TalkController@talkList');
        Route::post('talk/talk_list_create','TalkController@talkListCreate');
        Route::post('talk/finish','TalkController@finish');
        Route::post('talk/template_list','TalkController@templateList');
        Route::post('talk/template_list_create','TalkController@templateListCreate');
        Route::post('talk/template_list_change_status','TalkController@templateListChangeStatus');

        //***********帮助留言与反馈部分结束***********
    });


});
//内部使用，生成环境不用提交
Route::post('auth/get_token','Admin\V4\AuthController@getToken');

