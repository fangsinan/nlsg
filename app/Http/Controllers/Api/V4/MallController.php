<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MallGoods;
use App\Models\CouponRule;
use App\Models\MallComment;

class MallController extends Controller {

    /**
     * 获取商品信息
     * @api {post} /api/V4/goods/info 获取商品信息(列表,详情)
     * @apiVersion 4.0.0
     * @apiName /api/V4/goods/info
     * @apiGroup  Mall
     * @apiSampleRequest /api/V4/goods/info
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {string} ids_str 商品id,如果需要指定商品,则传该值(例:91,98)
     * @apiParam {number} [get_sku] 1:获取商品sku_list规格信息
     * @apiParam {number} [get_details] 1:获取商品详情,图片列表,服务说明
     * @apiParam {string} [cid] 商品分类,如需指定分类搜索则传该值(1,2,3)
     * @apiParam {string} [ob] 排序(new上架时间,sales售出,price价格,以上后缀分为_asc正序,_desc逆序.如果有ids_str可指定排序为ids_str,不传为默认)
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [get_all] 1:不设置分页,都传回
     * 
     * 
     * @apiSuccess {number} id 商品id
     * @apiSuccess {string} name 商品名称
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} picture 图片
     * @apiSuccess {number} original_price 原价
     * @apiSuccess {number} price 售价
     * @apiSuccess {string} category 分类名称
     * @apiSuccess {string} category_id 分类id
     * @apiSuccess {number} stock 库存
     * @apiSuccess {string} content 商品详情
     *
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
     * 
     * @apiSuccess {string[]} tos_list 服务说明
     * @apiSuccess {string} tos_list.title 标题
     * @apiSuccess {string} tos_list.content 内容
     * @apiSuccess {string} tos_list.icon 图标
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
     * @apiSuccess {number} sp_info.group_buy 是否有拼团,1有
     * @apiSuccess {number} sp_info.sp_type 当前商品特价表示(1:折扣  2:秒杀  3.凑单)
     * @apiSuccess {string[]} sp_info.list 所有活动类型列表([2,3,1])
     * 
     * 
     * @apiSuccessExample {json} Request-Example:
      {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "id": 91,
      "name": "AR立体浮雕星座地球仪",
      "subtitle": "高清生动准确的星座秘密等你来发现",
      "picture": "/nlsg/goods/20191026172620981048.jpg",
      "original_price": "379.00",
      "price": "5.00",
      "category": "益智玩具",
      "stock": "3327",
      "content": "<p><img src=\"http://share.nlsgapp.com/wechat/mall/goods/15205072688377.jpg\"></p>",
      "sku_list": [
      {
      "id": 1884,
      "goods_id": 91,
      "sku_number": "1612728266",
      "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
      "original_price": "379.00",
      "price": "9.70",
      "stock": 294,
      "sku_value": [
      {
      "sku_id": 1884,
      "key_name": "规格",
      "value_name": "AR立体浮雕星座地球仪"
      }
      ]
      }
      ],
      "picture_list": [
      {
      "id": 6711,
      "url": "/wechat/mall/goods/vg_20181208142653.jpg",
      "is_main": 0,
      "is_video": 0
      }
      ],
      "tos_list": [
      {
      "title": "7天可退还",
      "content": "不影响销售的话",
      "icon": "1.jpg"
      },
      {
      "title": "14天保修",
      "content": "不是人为损坏",
      "icon": ""
      }
      ],
      "active_group_list": {
      "1": {
      "id": 1,
      "title": "三八活动",
      "begin_time": "2020-06-01 00:00:00",
      "end_time": "2020-07-01 23:59:59",
      "ad_begin_time": "2020-05-12 00:00:00",
      "pre_begin_time": "2020-05-12 00:00:00",
      "lace_img": "",
      "wx_share_title": "微信三八标题",
      "wx_share_img": "wx38.jpg",
      "wx_share_desc": "微信三八简介"
      }
      },
      "twitter_money_list": [
      {
      "sku_number": "1612728266",
      "twitter_money": {
      "t_money_black": "2.00",
      "t_money_yellow": "3.00",
      "t_money_dealer": "4.00",
      "t_money": "1.00",
      "t_staff_money": 0
      }
      }
      ],
      "sp_info": {
      "group_buy": 1,
      "sp_type": 2,
      "list": [
      2,
      3,
      1,
      4
      ]
      }
      }
      ]
      }
     */
    public function goodsList(Request $request) {
        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 4;
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        $model = new MallGoods();
        $data = $model->getList($params, $user);
        return $this->success($data);
    }

    /**
     * 优惠券列表
     * @api {post} /api/V4/goods/coupon_list 优惠券列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/goods/coupon_list
     * @apiGroup  Mall
     * @apiSampleRequest /api/V4/goods/coupon_list
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {number} [goods_id] 指定商品id则返回无限制优惠券以及指定商品优惠券 
     * @apiParam {number} [goods_only] 1:如果指定goods_id,可通过该参数控制只返回指定商品优惠券
     * @apiParam {string} [ob] 排序(id上架时间,price价格,以上后缀分为_asc正序,_desc逆序.不传为默认)
     * @apiParam {nuimber} [show_zero_stock] 1:没有库存的也返回  默认不返回
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [get_all] 1:不设置分页,都传回 
     * 
     * 
      @apiSuccess {number} id id
      @apiSuccess {number} name 优惠券名称
      @apiSuccess {number} infinite 库存无限  1无限  0有限
      @apiSuccess {number} stock 库存
      @apiSuccess {number} price 面值
      @apiSuccess {number} full_cut 满减线,0表示无限制
      @apiSuccess {number} get_begin_time 开始领取时间
      @apiSuccess {number} get_end_time 领取结束时间
      @apiSuccess {number} past 领取后几天有效
      @apiSuccess {number} remarks 说明
      @apiSuccess {number} use_time_begin 有效期
      @apiSuccess {number} use_time_end 有效期
      @apiSuccess {number} can_use 是否能领取
     * 
     * 
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "id": 34,
      "name": "车速",
      "infinite": 0,
      "stock": 10,
      "price": "8.00",
      "restrict": 1,
      "full_cut": "0.00",
      "get_begin_time": 0,
      "get_end_time": 0,
      "past": "2",
      "use_type": 3,
      "remarks": "10",
      "use_time_begin": 0,
      "use_time_end": 0,
      "have_sub": 2,
      "can_use": 1
      }
      ]
      }
     */
    public function couponList(Request $request) {
        $model = new CouponRule();
        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 4;
        $data = $model->getList($params);
        return $this->success($data);
    }

    /**
     * 商品评论列表
     * @api {post} /api/V4/goods/comment_list 商品评论列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/goods/comment_list
     * @apiGroup  Mall
     * @apiSampleRequest /api/V4/goods/comment_list
     * @apiDescription 获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情
     * @apiParam {number} [goods_id] 指定商品id则返回无限制优惠券以及指定商品优惠券 
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * 
     * @apiSuccess {number} count 评论数量(只统计顶级评论数量)
     * @apiSuccess {string[]} list
      @apiSuccess {number} list.id 评论id
      @apiSuccess {number} list.user_id 用户id
      @apiSuccess {string} list.headimg 头像
      @apiSuccess {string} list.nick_name 昵称
      @apiSuccess {number} list.level 用户等级
      @apiSuccess {number} list.expire_time 等级到期时间
      @apiSuccess {string} list.content 评论内容
      @apiSuccess {number} list.ctime 评论时间
      @apiSuccess {number} list.star 星级
      @apiSuccess {string} list.reply_comment 官方回复内容
      @apiSuccess {string} list.reply_time 回复时间
      @apiSuccess {string} list.reply_nick_name 官方回复人昵称
      @apiSuccess {string[]} list.sku_value 规格值
     * 
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "count": 3,
      "list": [
      {
      "id": 951,
      "user_id": 168934,
      "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
      "nick_name": null,
      "level": 0,
      "expire_time": 0,
      "content": "12345",
      "ctime": 1578991712,
      "pid": 0,
      "goods_id": 91,
      "sku_number": "1612728266",
      "star": 5,
      "reply_comment": "感谢您的认可与支持，我们会不断提升产品质量和服务，为您营造更好的用户体验，欢迎您下次光临~",
      "reply_time": 1581652688,
      "reply_user_id": 2,
      "sku_id": 1884,
      "sku_value": [
      {
      "id": 364,
      "key_name": "规格",
      "value_name": "AR立体浮雕星座地球仪"
      }
      ],
      "list": [
      {
      "id": 923,
      "user_id": 168934,
      "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
      "nick_name": null,
      "level": 0,
      "expire_time": 0,
      "content": "测试测试",
      "ctime": 0,
      "pid": 951,
      "goods_id": 91,
      "sku_number": "7459726",
      "star": 3,
      "reply_comment": "",
      "reply_time": 0,
      "reply_user_id": 0,
      "sku_id": null,
      "sku_value": [],
      "list": [
      {
      "id": 925,
      "user_id": 168934,
      "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
      "nick_name": null,
      "level": 0,
      "expire_time": 0,
      "content": "质量好，实用，有趣，两个孩子非常喜欢！",
      "ctime": 1535506746,
      "pid": 923,
      "goods_id": 91,
      "sku_number": "1806683894",
      "star": 5,
      "reply_comment": "",
      "reply_time": 0,
      "reply_user_id": 0,
      "sku_id": 184,
      "sku_value": [],
      "list": []
      }
      ]
      }
      ]
      }
      ]
      }
      }
     */
    public function commentList(Request $request) {
        $model = new MallComment();
        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 4;
        $data = $model->getList($params);
        if ($data['code'] ?? true === false) {
            return $this->error($data['msg']);
        } else {
            return $this->success($data);
        }
    }
    
    public function hasTest(){
        $model = new MallGoods();
        $res = $model->has_test_goods();
        return $this->success($res);
    }

    //todo banner(轮播,分类下方的banner)
    //todo 推荐位(教学工具,家庭图书,时光文创)
    //todo 秒杀和拼团预告,秒杀和拼团首页
    //todo 拼团商品详情
    //todo 优选爆款

    //todo 商品购买说明(详情页下方)和商城首页服务说明(满88包邮等)
    //todo 建立免邮优惠券
    //todo 我的地址
}
