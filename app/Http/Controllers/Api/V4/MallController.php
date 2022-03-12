<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CouponRule;
use App\Models\CouponRuleList;
use App\Models\MallCategory;
use App\Models\MallComment;
use App\Models\MallGoods;
use App\Models\MallGoodsMsg;
use App\Models\RedeemCode;
use App\Models\SpecialPriceModel;
use App\Models\VipUser;
use App\Servers\ErpServers;
use App\Servers\MallRefundJob;
use App\Servers\V5\TempToolsServers;
use App\Servers\VipServers;
use App\Servers\VipWorksListServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MallController extends Controller
{
    /**
     * 获取商品信息
     * @api {get} /api/v4/goods/info 获取商品信息(列表,详情)
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/info
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/info
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {string} ids_str 商品id,如果需要指定商品,则传该值(例:91,98)
     * @apiParam {number=1,0} [get_sku] 1:获取商品sku_list规格信息
     * @apiParam {number=1,0} [get_details] 1:获取商品详情,图片列表,服务说明
     * @apiParam {string} [cid] 商品分类,如需指定分类搜索则传该值(1,2,3)
     * @apiParam {string} [zone_id] 商品专区id(banner接口返回的goods_list的id)
     * @apiParam {string} [ob] 排序(new上架时间,sales售出,price价格,以上后缀分为_asc正序,_desc逆序.如果有ids_str可指定排序为ids_str,不传为默认.chandler:热度:sales_desc;上新:new_asc)
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [get_all] 1:不设置分页,都传回
     *
     * @apiSuccess {number} id 商品id
     * @apiSuccess {string} name 商品名称
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} picture 图片
     * @apiSuccess {number} original_price 原价
     * @apiSuccess {number} price 售价
     * @apiSuccess {number} stock 库存
     * @apiSuccess {number} collect 1:已收藏 0:未收藏
     * @apiSuccess {string} content 商品详情
     *
     * @apiSuccess {string[]} cagetory_list 分类
     * @apiSuccess {string[]} cagetory_list.name 分类名称
     *
     * @apiSuccess {string[]} sku_list 规格列表
     * @apiSuccess {number} sku_list.id 规格id
     * @apiSuccess {string} sku_list.sku_number sku码
     * @apiSuccess {string} sku_list.picture 图片
     * @apiSuccess {string} sku_list.original_price 规格原价
     * @apiSuccess {string} sku_list.price 规格售价
     * @apiSuccess {string} sku_list.stock sku码
     * @apiSuccess {string} sku_list.sku_value sku值列表
     * @apiSuccess {number} sku_list.sku_value.sku_id skuid
     * @apiSuccess {string} sku_list.sku_value.key_name 规格名称
     * @apiSuccess {string} sku_list.sku_value.value_name 规格值
     *
     * @apiSuccess {string[]} picture_list 商品轮播图片(排序规则:视频,主图,其他)
     * @apiSuccess {number} picture_list.id 图片id
     * @apiSuccess {string} picture_list.url 图片地址
     * @apiSuccess {number} picture_list.is_main 1:主图
     * @apiSuccess {number} picture_list.is_video 1:表示是视频
     * @apiSuccess {number} picture_list.duration 视频时长(单位秒)
     * @apiSuccess {number} picture_list.cover_img 视频封面
     *
     * @apiSuccess {string[]} tos_bind_list 服务说明
     * @apiSuccess {string} tos_list.tos.title 标题
     * @apiSuccess {string} tos_list.tos.content 内容
     *
     * @apiSuccess {string[]} active_group_list 促销活动(可能多条,以第一条为准)
     * @apiSuccess {number} active_group_list.id 活动id
     * @apiSuccess {string} active_group_list.title 活动标题
     * @apiSuccess {string} active_group_list.begin_time 活动开始时间(2020-06-01 00:00:00)
     * @apiSuccess {string} active_group_list.end_time 活动结束时间(2020-07-01 23:59:59)
     * @apiSuccess {string} active_group_list.ad_begin_time 活动图标开始时间(2020-05-12 00:00:00)
     * @apiSuccess {string} active_group_list.pre_begin_time 活动预热开始时间(2020-05-12 00:00:00)
     * @apiSuccess {string} active_group_list.lace_img 活动图标
     * @apiSuccess {string} active_group_list.wx_share_title 分享标题
     * @apiSuccess {string} active_group_list.wx_share_img 分享图片
     * @apiSuccess {string} active_group_list.wx_share_desc 分享内容
     *
     * @apiSuccess {string[]} sp_info 商品特价详情
     * @apiSuccess {number} sp_info.group_buy 空表示没有拼团或多 不是空且price有值表有拼团和拼团的价格
     * @apiSuccess {number} sp_info.sp_type 当前商品特价表示(1:折扣  2:秒杀)
     * @apiSuccess {number} sp_info.begin_time 开始时间
     * @apiSuccess {number} sp_info.end_time 结束时间
     * @apiSuccess {string[]} sp_info.list 所有活动类型列表([2,1])
     *
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "original_price": "379.00",
     * "price": "5.00",
     * "category_id": 56,
     * "content": "<p><img src=\"http://share.nlsgapp.com/wechat/mall/goods/15205072688377.jpg\"></p>",
     * "active_group_list": {
     * "1": {
     * "id": 1,
     * "title": "三八活动",
     * "begin_time": "2020-06-01 00:00:00",
     * "end_time": "2020-07-01 23:59:59",
     * "ad_begin_time": "2020-05-12 00:00:00",
     * "pre_begin_time": "2020-05-12 00:00:00",
     * "lace_img": "",
     * "wx_share_title": "微信三八标题",
     * "wx_share_img": "wx38.jpg",
     * "wx_share_desc": "微信三八简介"
     * }
     * },
     * "twitter_money_list": [
     * {
     * "sku_number": "1612728266",
     * "twitter_money": {
     * "t_money_black": "2.00",
     * "t_money_yellow": "3.00",
     * "t_money_dealer": "4.00",
     * "t_money": "1.00",
     * "t_staff_money": 0
     * }
     * }
     * ],
     * "sku_list": [
     * {
     * "id": 1884,
     * "goods_id": 91,
     * "sku_number": "1612728266",
     * "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
     * "original_price": "379.00",
     * "price": "9.70",
     * "stock": 294,
     * "sku_value_list": [
     * {
     * "id": 364,
     * "sku_id": 1884,
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ]
     * }
     * ],
     * "sp_info": {
     * {
     * "group_buy": {
     * "price": "0.00",
     * "num": 10,
     * "begin_time": "2020-06-05 09:40:00",
     * "end_time": "2022-01-26 09:40:00"
     * },
     * "sp_type": 1,
     * "begin_time": "2020-06-04 20:16:45",
     * "end_time": "2020-07-11 00:00:00",
     * "list": [
     * 1,
     * 4
     * ]
     * }
     * },
     * "tos_bind_list": [
     * {
     * "goods_id": 91,
     * "tos_id": 1,
     * "tos": [
     * {
     * "title": "7天可退还",
     * "content": "不影响销售的话",
     * "icon": "1.jpg",
     * "id": 1
     * }
     * ]
     * },
     * {
     * "goods_id": 91,
     * "tos_id": 2,
     * "tos": [
     * {
     * "title": "14天保修",
     * "content": "不是人为损坏",
     * "icon": "",
     * "id": 2
     * }
     * ]
     * }
     * ],
     * "picture_list": [
     * {
     * "url": "/wechat/mall/goods/vg_20181208142653.jpg",
     * "is_main": 0,
     * "is_video": 0,
     * "duration": "",
     * "goods_id": 91,
     * "cover_img":""
     * }
     * ],
     * "category_list": {
     * "id": 56,
     * "name": "益智玩具"
     * }
     * }
     * ]
     * }
     */
    public function goodsList(Request $request) {
        if ($request->input('aa', 0) == 1) {
            dd(__LINE__);
            $open_360 = $request->input('open_360', 0);
            if ($open_360) {
                $list = DB::table('wwtest')->get()->toArray();
                $vs   = new VipServers();
                $res  = [];
                foreach ($list as $v) {
                    $temp               = [];
                    $temp['parent']     = $v->twitter;
                    $temp['phone']      = $v->phone;
                    $temp['send_money'] = 0;
                    $temp_res           = $vs->createVip_1($temp, 1);
                    $res[]              = $temp_res;
                    if ($temp_res['code']) {
                        DB::table('wwtest')->where('id', '=', $v->id)->delete();
                    }
                }
                dd([$res, $list]);
            }

            $wx_test = $request->input('wx_test', 0);
            if ($wx_test) {

//                $t_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxe24a425adb5102f6&secret=2ded804b74f99ae2f342423dd7952620";
//                $t_res = Http::get($t_url);
                $as = '49_kDKgl_TFwjk-9hbwlaKhXpBDjWUrNNpUXG1dSabUQ7HChbjRPQZ008vd9vrN35CXEXSqzsIDpy3T5H-Q_i7v1g';

                $url = "https://api.weixin.qq.com/cgi-bin/user/info";
                $res = Http::get($url, [
                    'access_token' => $as,
                    'openid'       => 'oVWHQwXqOy6POy8z2IVHz-RgRsZ0',
                    'lang'         => 'zh_CN',
                ]);

                return json_decode($res, true);

            }
            $shill_check = $request->input('shill_check', 0);
            if ($shill_check === '1') {
                MallRefundJob::shillJob(1);
            }
            if ($shill_check === '2') {
                MallRefundJob::shillJob(2);
            }
//            set_time_limit(0);
//            $llModel = new LiveLogin();
//            $llModel->clear();

//            $servers = new removeDataServers();
//            $servers->worksListOfSub();
//            MallOrder::testRun(1);
//            $s = new ImDocServers();
//            $r = $s->sendGroupDocMsgJob(701);

            dd(__LINE__ . date('Y-m-d H:i:s'));


//            dd($r);
//            $servers = new removeDataServers();
//            $servers->worksListOfSub();
//            $s = new ErpServers();
//            $s->pushRun();
//            $s->logisticsSync();
//            ChannelServers::cytxJob();

//            $res = Live::teamInfo(1,1);
//            return $this->getRes($res);
//                MallRefundJob::shillJob(1);
//                VipRedeemUser::subWorksOrGetRedeemCode(726128);
//                VipRedeemUser::subWorksOrGetRedeemCode(731016);
//                VipRedeemUser::subWorksOrGetRedeemCode(270277);
//                VipRedeemUser::subWorksOrGetRedeemCode(740418);
//                VipRedeemUser::subWorksOrGetRedeemCode(740420);


//                $c = new ChannelServers();
//                $c->getDouyinOrder();//获取抖音订单
//                $c->supplementDouYinOrder();//补全订单信息
//                $c->douYinJob();

            if (0) {
                $data = [
                    'out_trade_no'   => '21040900168934727026601', //获取订单号
                    'total_fee'      => 0.01, //价格
                    'transaction_id' => 88888888888, //交易单号
                    'attach'         => 8,
                    'pay_type'       => 1,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
                ];
                $res  = WechatPay::PayStatusUp($data);
                dd($res);
            }

            if (0) {
                $vipModel = new VipUser();
                $res      = $vipModel->jobOf1360(168934, 376481, 645);
                dd($res);
            }

            exit(date('Y-m-d H:i:s'));
        } else {
            $params         = $request->input();
            $params['page'] = $params['page'] ?? 1;
            $params['size'] = $params['size'] ?? 10;
            $model          = new MallGoods();
            $data           = $model->getList($params, $this->user);
            return $this->success($data);
        }
    }

    /**
     * 优惠券列表
     * @api {get} /api/v4/goods/coupon_list 优惠券列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/coupon_list
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/coupon_list
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {number} [goods_id] 指定商品id则返回无限制优惠券以及指定商品优惠券
     * @apiParam {number} [goods_only] 1:如果指定goods_id,可通过该参数控制只返回指定商品优惠券
     * @apiParam {string} [ob] 排序(id上架时间,price价格,以上后缀分为_asc正序,_desc逆序.不传为默认)
     * @apiParam {number} [show_zero_stock] 1:没有库存的也返回  默认不返回
     * @apiParam {number=1,0} [get_all] 1:不设置分页,都传回
     *
     * @apiSuccess {number} id id
     * @apiSuccess {number} name 优惠券名称
     * @apiSuccess {number} infinite 库存无限  1无限  0有限
     * @apiSuccess {number} stock 库存
     * @apiSuccess {number} price 面值
     * @apiSuccess {number} full_cut 满减线,0表示无限制
     * @apiSuccess {number} get_begin_time 开始领取时间
     * @apiSuccess {number} get_end_time 领取结束时间
     * @apiSuccess {number} past 领取后几天有效
     * @apiSuccess {number} remarks 说明
     * @apiSuccess {number} use_time_begin 有效期
     * @apiSuccess {number} use_time_end 有效期
     * @apiSuccess {number} can_use 是否能领取
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 34,
     * "name": "车速",
     * "infinite": 0,
     * "stock": 10,
     * "price": "8.00",
     * "restrict": 1,
     * "full_cut": "0.00",
     * "get_begin_time": 0,
     * "get_end_time": 0,
     * "past": "2",
     * "use_type": 3,
     * "remarks": "10",
     * "use_time_begin": 0,
     * "use_time_end": 0,
     * "have_sub": 2,
     * "can_use": 1
     * }
     * ]
     * }
     */
    public function couponList(Request $request) {
        $model          = new CouponRule();
        $params         = $request->input();
        $params['page'] = 1;
        $params['size'] = 4;
        $data           = $model->getList($params, $this->user['id'] ?? 0);
        return $this->success($data);
    }

    /**
     * 商品评论列表
     * @api {get} /api/v4/goods/comment_list 商品评论列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/comment_list
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/comment_list
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {number} goods_id 指定商品id
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     *
     * @apiSuccess {number} count 评论数量(只统计顶级评论数量)
     * @apiSuccess {string[]} list
     * @apiSuccess {number} list.id 评论id
     * @apiSuccess {number} list.user_id 用户id
     * @apiSuccess {string} list.headimg 头像
     * @apiSuccess {string} list.nick_name 昵称
     * @apiSuccess {number} list.level 用户等级
     * @apiSuccess {number} list.expire_time 等级到期时间
     * @apiSuccess {string} list.content 评论内容
     * @apiSuccess {number} list.ctime 评论时间
     * @apiSuccess {number} list.star 星级
     * @apiSuccess {string} list.reply_comment 官方回复内容
     * @apiSuccess {string} list.reply_time 回复时间
     * @apiSuccess {string} list.reply_nick_name 官方回复人昵称
     * @apiSuccess {string[]} list.sku_value 规格值
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "count": 3,
     * "list": [
     * {
     * "id": 951,
     * "user_id": 168934,
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
     * "nick_name": null,
     * "level": 0,
     * "expire_time": 0,
     * "content": "12345",
     * "ctime": 1578991712,
     * "pid": 0,
     * "goods_id": 91,
     * "sku_number": "1612728266",
     * "star": 5,
     * "reply_comment": "感谢您的认可与支持，我们会不断提升产品质量和服务，为您营造更好的用户体验，欢迎您下次光临~",
     * "reply_time": 1581652688,
     * "reply_user_id": 2,
     * "sku_id": 1884,
     * "sku_value": [
     * {
     * "id": 364,
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "list": [
     * {
     * "id": 923,
     * "user_id": 168934,
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
     * "nick_name": null,
     * "level": 0,
     * "expire_time": 0,
     * "content": "测试测试",
     * "ctime": 0,
     * "pid": 951,
     * "goods_id": 91,
     * "sku_number": "7459726",
     * "star": 3,
     * "reply_comment": "",
     * "reply_time": 0,
     * "reply_user_id": 0,
     * "sku_id": null,
     * "sku_value": [],
     * "list": [
     * {
     * "id": 925,
     * "user_id": 168934,
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
     * "nick_name": null,
     * "level": 0,
     * "expire_time": 0,
     * "content": "质量好，实用，有趣，两个孩子非常喜欢！",
     * "ctime": 1535506746,
     * "pid": 923,
     * "goods_id": 91,
     * "sku_number": "1806683894",
     * "star": 5,
     * "reply_comment": "",
     * "reply_time": 0,
     * "reply_user_id": 0,
     * "sku_id": 184,
     * "sku_value": [],
     * "list": []
     * }
     * ]
     * }
     * ]
     * }
     * ]
     * }
     * }
     */
    public function commentList(Request $request) {
        $model  = new MallComment();
        $params = $request->input();
        $data   = $model->getList($params);
        return $this->getRes($data);
    }

    /**
     * 商品分类列表
     * @api {get} /api/v4/goods/category_list 商品分类列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/category_list
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/category_list
     * @apiDescription 获取商品分类列表
     *
     * @apiSuccess {number} id id
     * @apiSuccess {number} name 名称
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 40,
     * "name": "家庭育儿"
     * },
     * {
     * "id": 41,
     * "name": "夫妻关系"
     * },
     * {
     * "id": 42,
     * "name": "心理励志"
     * }
     * ]
     * }
     */
    public function categoryList() {
        $model = new MallCategory();
        $data  = $model->getUsedList();
        return $this->success($data);
    }

    /**
     * 商城首页banner和推荐位
     * @api {get} /api/v4/goods/banner_list 商城banner
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/banner_list
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/banner_list
     * @apiDescription 轮播,分类下方的banner,推荐的商品集
     *
     * @apiSuccess {number} banner banner轮播的
     * @apiSuccess {number} banner.id
     * @apiSuccess {number} banner.title
     * @apiSuccess {number} banner.pic
     * @apiSuccess {number} banner.url
     * @apiSuccess {number} banner.jump_type 跳转类型(1:h5(走url),2商品,3优惠券领取页面)
     * @apiSuccess {number} banner.obj_id 跳转目标id
     *
     * @apiSuccess {number} recommend 下方推荐位(字段同banner)
     * @apiSuccess {number} hot_sale 爆款推荐(字段同banner)
     *
     * @apiSuccess {number} goods_list 推荐的商品专区
     * @apiSuccess {number} goods_list.id
     * @apiSuccess {number} goods_list.icon 图标
     * @apiSuccess {number} goods_list.name  名称
     * @apiSuccess {number} goods_list.ids_str  专区商品id
     *
     * @apiSuccess {number} postage_line  包邮线
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598518779,
     * "data": {
     * "banner": [
     * {
     * "id": 407,
     * "title": "儿童情商社交游戏绘本",
     * "pic": "wechat/mall/goods/20190110155407_333.png",
     * "url": "/mall/shop-details?goods_id=333",
     * "jump_type": 0,
     * "obj_id": 0
     * },
     * {
     * "id": 406,
     * "title": "乌合之众",
     * "pic": "wechat/mall/goods/20190110155401_327.png",
     * "url": "/mall/shop-details?goods_id=327",
     * "jump_type": 2,
     * "obj_id": 91
     * }
     * ],
     * "recommend": [
     * {
     * "id": 412,
     * "title": "活动测试",
     * "pic": "nlsg/banner/20200521142524320648.png",
     * "url": "/pages/activity/sixOne",
     * "jump_type": 0,
     * "obj_id": 0
     * },
     * {
     * "id": 408,
     * "title": "欢乐中国年",
     * "pic": "wechat/mall/goods/20190110155411_338.png",
     * "url": "/mall/shop-details?goods_id=338",
     * "jump_type": 0,
     * "obj_id": 0
     * }
     * ],
     * "goods_list": [
     * {
     * "id": 2,
     * "name": "教学工具",
     * "icon": "nlsg/goods/20200827113651486038.png",
     * "ids_str": "156,159,160,161,163,164,165,166,168,184,188,189,191,194,196,197,202,205,209,218"
     * },
     * {
     * "id": 3,
     * "name": "家庭图书",
     * "icon": "nlsg/goods/20200827114214174967.png",
     * "ids_str": "230,231,255,261,262,263,265,324,325,327"
     * }
     * ],
     * "postage_line": "88"
     * }
     * }
     */
    public function bannerList() {
        $model = new Banner();
        $data  = $model->mallBannerList();
        return $this->success($data);
    }

    /**
     * 秒杀和拼团预告
     * @api {get} /api/v4/goods/home_sp_list 秒杀和拼团预告
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/home_sp_list
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/home_sp_list
     * @apiDescription 秒杀和拼团预告
     *
     * @apiSuccess {number} sec 秒杀的
     * @apiSuccess {number} sec.time 开始时间
     * @apiSuccess {number} sec.list 商品列表
     * @apiSuccess {number} sec.list.goods_id 商品id
     * @apiSuccess {number} sec.list.name 名称
     * @apiSuccess {number} sec.list.subtitle 副标题
     * @apiSuccess {number} sec.list.group_num 拼团需要人数
     * @apiSuccess {number} sec.list.group_price 拼团价格
     * @apiSuccess {number} sec.list.begin_time 开始时间
     * @apiSuccess {number} sec.list.end_time 结束时间
     *
     *
     * @apiSuccess {number} group 拼团的
     * @apiSuccess {number} group.goods_id 商品id
     * @apiSuccess {number} group.name 名称
     * @apiSuccess {number} group.subtitle 副标题
     * @apiSuccess {number} group.group_num 拼团需要人数
     * @apiSuccess {number} group.group_price 拼团价格
     * @apiSuccess {number} group.begin_time 开始时间
     * @apiSuccess {number} group.end_time 结束时间
     * @apiSuccess {number=1,2} group.is_begin 是否开始(1开始 0未开始)
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "sec": {
     * "time": "2020-06-11 17:34:00",
     * "list": [
     * {
     * "goods_id": 86,
     * "name": "AR智能学生专用北斗地球仪",
     * "subtitle": "王树声地理教学研究室倾力打造地理教学地球仪",
     * "goods_original_price": "0.00",
     * "original_price": "379.00",
     * "goods_price": "0.00",
     * "begin_time": "2020-06-11 17:34:00",
     * "end_time": "2020-06-11 17:52:59"
     * }
     * ]
     * },
     * "group": [
     * {
     * "goods_id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "group_num": 10,
     * "group_price": "20.00",
     * "begin_time": "2020-06-05 09:40:00",
     * "end_time": "2022-01-26 09:40:00"
     * },
     * {
     * "goods_id": 86,
     * "name": "AR智能学生专用北斗地球仪",
     * "subtitle": "王树声地理教学研究室倾力打造地理教学地球仪",
     * "group_num": 5,
     * "group_price": "18.00",
     * "begin_time": "2020-06-05 09:36:17",
     * "end_time": "2022-01-26 09:40:00"
     * }
     * ]
     * }
     * }
     */
    public function homeSpList() {
        $model         = new SpecialPriceModel();
        $data['sec']   = $model->homeSecList();
        $data['group'] = $model->homeGroupList();
        $data['now']   = time();
        return $this->success($data);
    }

    /**
     * 秒杀首页
     * @api {get} /api/v4/goods/flash_sale 秒杀首页
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/flash_sale
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/flash_sale
     * @apiDescription 秒杀首页
     *
     * @apiSuccess {string} show_time 时间
     * @apiSuccess {number} statis 状态
     * @apiSuccess {string[]} data 列表
     * @apiSuccess {number} data.goods_id 商品id
     * @apiSuccess {number} data.name 名称
     * @apiSuccess {number} data.subtitle 副标题
     * @apiSuccess {number} data.group_num 拼团需要人数
     * @apiSuccess {number} data.group_price 拼团价格
     * @apiSuccess {number} data.begin_time 开始时间
     * @apiSuccess {number} data.end_time 结束时间
     * @apiSuccess {number} data.stock 库存
     * @apiSuccess {number} data.use_stock 已用库存
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "time": "2020-07-30 18:12:00",
     * "show_time": "18:12",
     * "timestamp": 1596103920,
     * "status": "即将开抢",
     * "data": [
     * {
     * "goods_id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "goods_original_price": "0.00",
     * "original_price": "379.00",
     * "stock": 0,
     * "use_stock": 1,
     * "goods_price": "5.00",
     * "begin_time": "2020-07-30 18:12:00",
     * "end_time": "2020-08-28 18:26:59",
     * "begin_timestamp": 1596103920,
     * "end_timestamp": 1598610419
     * }
     * ]
     * },
     * {
     * "time": "2020-07-31 18:12:00",
     * "show_time": "18:12",
     * "timestamp": 1596190320,
     * "status": "即将开抢",
     * "data": [
     * {
     * "goods_id": 98,
     * "name": "王琨专栏学习套装",
     * "subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
     * "goods_original_price": "0.00",
     * "original_price": "399.00",
     * "stock": 0,
     * "use_stock": 0,
     * "goods_price": "9.90",
     * "begin_time": "2020-07-31 18:12:00",
     * "end_time": "2020-08-28 18:26:59",
     * "begin_timestamp": 1596190320,
     * "end_timestamp": 1598610419
     * }
     * ]
     * }
     * ]
     * }
     */
    public function flashSaleList() {
        $model = new SpecialPriceModel();
        $data  = $model->getSecList(2);
        return $this->success($data);
    }

    /**
     * 拼团首页
     * @api {get} /api/v4/goods/group_buy 拼团首页
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/group_buy
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy
     * @apiDescription 拼团首页
     *
     * @apiSuccess {number} goods_id 商品id
     * @apiSuccess {number} name 名称
     * @apiSuccess {number} subtitle 副标题
     * @apiSuccess {number} group_num 拼团需要人数
     * @apiSuccess {number} group_price 拼团价格
     * @apiSuccess {number} begin_time 开始时间
     * @apiSuccess {number} end_time 结束时间
     * @apiSuccess {number} user_count 参加人数
     * @apiSuccess {number=1,2} is_begin 是否开始(1开始 0未开始)
     * @apiSuccess {string[]} order_user 用户头像列表
     * @apiSuccessExample {json} Request-Example:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "goods_id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "group_num": 10,
     * "group_price": "20.00",
     * "begin_time": "2020-06-05 09:40:00",
     * "end_time": "2022-01-26 09:40:00",
     * "user_count": 4,
     * "order_user": [
     * "1.jpg",
     * "1.jpg",
     * "1.jpg",
     * "1.jpg"
     * ]
     * }
     * ]
     * }
     */
    public function groupBuyList() {
        $model = new SpecialPriceModel();
        $data  = $model->groupBuyList();
        return $this->success($data);
    }

    /**
     * 商城服务说明
     * @api {get} /api/v4/goods/service_description 商城服务说明
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/service_description
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/service_description
     * @apiDescription 商城服务说明
     * @apiSuccessExample {json} Request-Example:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "k": "七天无理由退换货",
     * "v": "买家提出退款申请所指向的商品"
     * },
     * {
     * "k": "正品保障",
     * "v": "正品保障服务是指"
     * },
     * {
     * "k": "会员85折",
     * "v": "成为能量时光皇钻会员"
     * },
     * {
     * "k": "满88包邮",
     * "v": "能量时光自营商品"
     * }
     * ]
     * }
     */
    public function mallServiceDescription() {
        $model = new MallGoods();
        $res   = $model->mallServiceDescription();
        return $this->success($res);
    }

    /**
     * 商城购买须知
     * @api {get} /api/v4/goods/buyer_reading 商城购买须知
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/buyer_reading
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/buyer_reading
     * @apiDescription 商城服务说明
     * @apiSuccessExample {json} Request-Example:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "k": "关于发货",
     * "v": "发货以订单拍下的商品及颜色为准，付款后2个工作日内发货。"
     * },
     * {
     * "k": "售后服务电话：010-85164891",
     * "v": ""
     * }
     * ]
     * }
     */
    public function buyerReading() {
        $model = new MallGoods();
        $res   = $model->buyerReading();
        return $this->success($res);
    }

    /**
     * 拼团购买须知
     * @api {get} /api/v4/goods/buyer_reading_gb 拼团购买须知
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/buyer_reading_gb
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/buyer_reading_gb
     * @apiDescription 拼团购买须知
     */
    public function buyerReadingForGroupBuy() {
        $res = \App\Models\ConfigModel::getData(17);
        $res = json_decode($res);
        return $this->success($res);
    }

    /**
     * 拼团商品详情
     * @api {get} /api/v4/goods/group_buy_info 拼团商品详情
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/group_buy_info
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_info
     * @apiDescription 拼团商品详情(返回值参考商品详情. group_num拼团需要人数 order_numn已拼人数 normal_price单独购买价格,goods和sku都有这个字段)
     * @apiSuccess {number} group_buy_id 拼团列表id
     */
    public function groupByGoodsInfo(Request $request) {
        $params = $request->input();
        $model  = new MallGoods();
        $data   = $model->groupByGoodsInfo($params, $this->user);

        return $this->getRes($data);
    }

    /**
     * 收藏
     * @api {post} /api/v4/goods/collect 收藏
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/collect
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/collect
     * @apiDescription 收藏,取消收藏
     * @apiParam {number} goods_id 商品id
     *
     */
    public function collect(Request $request) {
        $goods_id = $request->input('goods_id', 0);
        $model    = new MallGoods();
        $data     = $model->collect($goods_id, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 猜你喜欢
     * @api {get} /api/v4/goods/for_your_reference 猜你喜欢
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/for_your_reference
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/for_your_reference
     * @apiDescription 猜你喜欢(参数同商品列表接口)
     * @apiParam {number} [num] 显示数量
     *
     */
    public function forYourReference(Request $request) {
        $num   = $request->input('num', 4);
        $model = new MallGoods();
        $data  = $model->forYourReference($num, $this->user);
        return $this->getRes($data);
    }

    /**
     * 优惠券领取页面
     * @api {post} /api/v4/mall_coupon/rule 优惠券领取页面
     * @apiVersion 4.0.0
     * @apiName /api/v4/mall_coupon/rule
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall_coupon/rule
     * @apiDescription 优惠券领取页面
     * @apiParam {number} id id
     *
     */
    public function getCouponList(Request $request) {
        $id    = $request->input('id', 0);
        $model = new CouponRuleList();
        $data  = $model->list($id, $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 到货提醒
     * @api {post} /api/v4/goods/sub 到货提醒
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/sub
     * @apiGroup  Mall
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/sub
     * @apiDescription 到货提醒,假接口
     * @apiParam {number} [sku_number] sku编码
     * @apiParam {number} goods_id 商品id
     *
     */
    public function sub(Request $request) {
        $model = new MallGoodsMsg();
        $data  = $model->add($request->input(), $this->user);
        return $this->getRes($data);
    }

    /**
     * 兑换码
     * @api {post} /api/v4/home/redeem_code 兑换码
     * @apiVersion 4.0.0
     * @apiName /api/v4/home/redeem_code
     * @apiGroup  Home
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/home/redeem_code
     * @apiDescription 兑换码
     * @apiParam {number} code 兑换码
     * @apiParam {number=1,2,3} os_type 系统( 1 安卓 2ios 3微信)
     *
     */
    public function redeemCode(Request $request) {
        $model = new RedeemCode();
        $data  = $model->redeem($request->input(), $this->user);
        return $this->getRes($data);
    }

    /**
     * 兑换码列表
     * @api {get} /api/v4/home/redeem_code_list 兑换码列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/home/redeem_code_list
     * @apiGroup  Home
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/home/redeem_code_list
     * @apiDescription 兑换码列表
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     * @apiParam {number} status 筛选(不传都饭,1是已使用,0是未使用)
     *
     */
    public function redeemCodeList(Request $request) {
        $model = new RedeemCode();
        $data  = $model->redeemList($request->input(), $this->user);
        return $this->getRes($data);
    }
}
