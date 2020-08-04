<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\MallOrder;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Models\MallComment;
use Illuminate\Http\Request;

class MallOrderController extends Controller
{

    /**
     * 预下单
     * @api {post} /api/v4/mall/prepare_create_order 普通订单预下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/prepare_create_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_order
     * @apiDescription 普通订单预下单
     * @apiParam {number=1,2} from_cart 下单方式(1:购物车下单  2:立即购买
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_goods_id 优惠券id,没有0
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "from_cart":1,
     * "sku":"1612728266,1835913656,1654630825,1626220663",
     * "goods_id":209,
     * "buy_num":1,
     * "inviter":211172,
     * "post_type":1,
     * "coupon_goods_id":0,
     * "coupon_freight_id":0,
     * "address_id":2814,
     * "os_type":1
     * }
     *
     * @apiSuccess {string[]} sku_list 商品信息
     * @apiSuccess {string} sku_list.name 商品名称
     * @apiSuccess {string} sku_list.subtitle 副标题
     * @apiSuccess {string} sku_list.picture 图片
     * @apiSuccess {string[]} sku_list.sku_value_list 规格
     * @apiSuccess {number} sku_list.num 数量
     * @apiSuccess {string} sku_list.original_price 原价
     * @apiSuccess {string} sku_list.price 售价
     *
     * @apiSuccess {string[]} price_list 订单价格
     * @apiSuccess {number} price_list.all_original_price 原价
     * @apiSuccess {number} price_list.all_price 售价
     * @apiSuccess {number} price_list.freight_money 邮费
     * @apiSuccess {number} price_list.vip_cut_money 权益立减
     * @apiSuccess {number} price_list.sp_cut_money 活动立减
     * @apiSuccess {number} price_list.coupon_money 优惠券立减
     * @apiSuccess {number} price_list.order_price 订单金额
     *
     * @apiSuccess {string[]} address_list 用户地址列表
     *
     * @apiSuccess {string[]} coupon_list 可用优惠券列表
     * @apiSuccess {string[]} coupon_list.coupon_goods 商品优惠券列表
     * @apiSuccess {string[]} coupon_list.coupon_freight 免邮券列表
     *
     * @apiSuccess {number} name 名称
     * @apiSuccess {number} subtitle 副标题
     * @apiSuccessExample {json} Request-Example:
     * {
     * "sku_list": [
     * {
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
     * "sku_value_list": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "num": 2,
     * "original_price": "379.00",
     * "price": "9.70"
     * },
     * {
     * "name": "王琨专栏学习套装",
     * "subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
     * "picture": "/wechat/mall/goods/8873_1545796221.png",
     * "sku_value_list": [
     * {
     * "key_name": "规格",
     * "value_name": "王琨专栏学习套装"
     * }
     * ],
     * "num": 1,
     * "original_price": "399.00",
     * "price": "254.15"
     * }
     * ],
     * "price_list": {
     * "all_original_price": "1789.00",
     * "all_price": "746.27",
     * "freight_money": "13.00",
     * "vip_cut_money": "304.13",
     * "sp_cut_money": "738.60",
     * "coupon_money": 0,
     * "freight_free_flag": false,
     * "order_price": "759.27"
     * },
     * "address_list": [
     * {
     * "id": 2815,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 1,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * }
     * ],
     * "coupon_list": {
     * "coupon_goods": [
     * {
     * "id": 7,
     * "name": "5元优惠券(六一专享)",
     * "type": 3,
     * "price": "5.00",
     * "full_cut": "0.00",
     * "explain": "六一活动期间",
     * "begin_time": "2020-06-12 00:00:00",
     * "end_time": "2020-06-19 23:59:59"
     * }
     * ],
     * "coupon_freight": [
     * {
     * "id": 10,
     * "name": "测试免邮券",
     * "type": 4,
     * "price": "0.00",
     * "full_cut": "0.00",
     * "explain": "商品免邮券",
     * "begin_time": "2020-06-12 00:00:00",
     * "end_time": "2020-06-22 23:59:59"
     * }
     * ]
     * }
     * }
     */
    public function prepareCreateOrder(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $params['from_cart'] = $request->input('from_cart', 9);
        $params['sku'] = $request->input('sku', '');
        $params['goods_id'] = $request->input('goods_id', 0);
        $params['buy_num'] = $request->input('buy_num', 0);
        $params['inviter'] = $request->input('inviter', 0);
        $params['post_type'] = $request->input('post_type', 0);
        $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
        $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
        $params['address_id'] = $request->input('address_id', 0);
        $params['os_type'] = $request->input('os_type', 0);

        $model = new MallOrder();
        $data = $model->prepareCreateOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 下单
     * @api {post} /api/v4/mall/create_order 普通订单下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/create_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/create_order
     * @apiDescription 普通订单下单(参数同预下单)
     * @apiParam {number=1,2} from_cart 下单方式(1:购物车下单  2:立即购买
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_goods_id 优惠券id,没有0
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "order_id": 9530,
     * "ordernum": "2006190016893457221111"
     * }
     * }
     */
    public function createOrder(Request $request)
    {
        if (0) {
            $params['from_cart'] = 1;
            $params['sku'] = '1612728266,1835913656,1654630825,1626220663';
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = 1;
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = 2814;
            $params['os_type'] = 1;
        } else {
            $params['from_cart'] = $request->input('from_cart', 9);
            $params['sku'] = $request->input('sku', '');
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = $request->input('post_type', 0);
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 0);
            $params['os_type'] = $request->input('os_type', 0);
        }

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $model = new MallOrder();
        $data = $model->createOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 秒杀订单预下单
     * @api {post} /api/v4/mall/prepare_create_flash_sale_order 秒杀订单预下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/prepare_create_flash_sale_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_flash_sale_order
     * @apiDescription 秒杀订单预下单
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "sku":"1612728266",
     * "goods_id":209,
     * "buy_num":1,
     * "inviter":211172,
     * "post_type":1,
     * "coupon_freight_id":0,
     * "address_id":2814,
     * "os_type":1
     * }
     *
     * @apiSuccess {string[]} sku_list 商品信息
     * @apiSuccess {string} sku_list.name 商品名称
     * @apiSuccess {string} sku_list.subtitle 副标题
     * @apiSuccess {string} sku_list.picture 图片
     * @apiSuccess {string[]} sku_list.sku_value_list 规格
     * @apiSuccess {number} sku_list.num 数量
     * @apiSuccess {string} sku_list.original_price 原价
     * @apiSuccess {string} sku_list.price 售价
     *
     * @apiSuccess {string[]} price_list 订单价格
     * @apiSuccess {number} price_list.all_original_price 原价
     * @apiSuccess {number} price_list.all_price 售价
     * @apiSuccess {number} price_list.freight_money 邮费
     * @apiSuccess {number} price_list.sp_cut_money 活动立减
     * @apiSuccess {number} price_list.coupon_money 优惠券立减
     * @apiSuccess {number} price_list.order_price 订单金额
     * @apiSuccess {string[]} address_list 用户地址列表
     * @apiSuccess {string[]} coupon_freight_list 免邮券列表
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "user": {
     * "id": 168934,
     * "level": 4,
     * "is_staff": 1
     * },
     * "sku_list": {
     * "goods_id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
     * "sku_value_list": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "num": 2,
     * "original_price": "379.00",
     * "price": "5.00"
     * },
     * "price_list": {
     * "all_original_price": "758.00",
     * "all_price": "10.00",
     * "freight_money": "13.00",
     * "sp_cut_money": "748.00",
     * "freight_free_flag": false,
     * "order_price": "23.00"
     * },
     * "address_list": [
     * {
     * "id": 2815,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 1,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * },
     * {
     * "id": 2814,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 0,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * }
     * ],
     * "coupon_freight_list": [
     * {
     * "id": 10,
     * "name": "测试免邮券",
     * "type": 4,
     * "price": "0.00",
     * "full_cut": "0.00",
     * "explain": "商品免邮券",
     * "begin_time": "2020-06-12 00:00:00",
     * "end_time": "2020-06-28 23:59:59",
     * "cr_id": 35,
     * "sub_list": []
     * }
     * ],
     * "used_address": {
     * "id": 2814,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 0,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * }
     * }
     * }
     */
    public function prepareCreateFlashSaleOrder(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        if (0) {
            $params['sku'] = '1612728266';
            $params['goods_id'] = 91;
            $params['buy_num'] = intval($request->input('buy_num', 2));
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = 1;
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 2814);
            $params['os_type'] = 1;
        } else {
            $params['sku'] = $request->input('sku', '');
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = $request->input('post_type', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 0);
            $params['os_type'] = $request->input('os_type', 0);
        }

        $model = new MallOrderFlashSale();
        $data = $model->prepareCreateFlashSaleOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 秒杀订单下单
     * @api {post} /api/v4/mall/create_flash_sale_order 秒杀订单下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/create_flash_sale_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/create_flash_sale_order
     * @apiDescription 秒杀订单下单(参数同预下单)
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "order_id": 9530,
     * "ordernum": "2006190016893457221111"
     * }
     * }
     */
    public function CreateFlashSaleOrder(Request $request)
    {
        if (0) {
            $params['sku'] = '1612728266';
            $params['goods_id'] = 91;
            $params['buy_num'] = intval($request->input('buy_num', 2));
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = 1;
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 2814);
            $params['os_type'] = 1;
        } else {
            $params['sku'] = $request->input('sku', '');
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = $request->input('post_type', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 0);
            $params['os_type'] = $request->input('os_type', 0);
        }

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $model = new MallOrderFlashSale();
        $data = $model->createFlashSaleOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团订单预下单
     * @api {post} /api/v4/mall/prepare_create_group_buy_order 拼团订单预下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/prepare_create_group_buy_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_group_buy_order
     * @apiDescription 拼团订单预下单
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     * @apiParam {buy_type=1,2} buy_type 1开团 2参团
     * @apiParam {number} group_key 如果是参团,需要传
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "sku":"1612728266",
     * "goods_id":209,
     * "buy_num":1,
     * "inviter":211172,
     * "post_type":1,
     * "coupon_goods_id":0,
     * "coupon_freight_id":0,
     * "address_id":2814,
     * "os_type":1,
     * "buy_type":1,
     * "group_key":1
     * }
     *
     * @apiSuccess {string[]} sku_list 商品信息
     * @apiSuccess {string} sku_list.name 商品名称
     * @apiSuccess {string} sku_list.subtitle 副标题
     * @apiSuccess {string} sku_list.picture 图片
     * @apiSuccess {string[]} sku_list.sku_value_list 规格
     * @apiSuccess {number} sku_list.num 数量
     * @apiSuccess {string} sku_list.original_price 原价
     * @apiSuccess {string} sku_list.price 售价
     *
     * @apiSuccess {string[]} price_list 订单价格
     * @apiSuccess {number} price_list.all_original_price 原价
     * @apiSuccess {number} price_list.all_price 售价
     * @apiSuccess {number} price_list.freight_money 邮费
     * @apiSuccess {number} price_list.sp_cut_money 活动立减
     * @apiSuccess {number} price_list.coupon_money 优惠券立减
     * @apiSuccess {number} price_list.order_price 订单金额
     * @apiSuccess {string[]} address_list 用户地址列表
     * @apiSuccess {string[]} coupon_freight_list 免邮券列表
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "user": {
     * "id": 168934,
     * "level": 4,
     * "is_staff": 1
     * },
     * "sku_list": {
     * "goods_id": 91,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
     * "sku_value_list": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "num": 2,
     * "original_price": "379.00",
     * "price": "20.00"
     * },
     * "price_list": {
     * "all_original_price": "758.00",
     * "all_price": "40.00",
     * "freight_money": "13.00",
     * "vip_cut_money": 0,
     * "sp_cut_money": "718.00",
     * "coupon_money": 0,
     * "freight_free_flag": false,
     * "order_price": "53.00"
     * },
     * "address_list": [
     * {
     * "id": 2815,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 1,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * }
     * ],
     * "coupon_list": {
     * "coupon_goods": [
     * {
     * "id": 7,
     * "name": "5元优惠券(六一专享)",
     * "type": 3,
     * "price": "5.00",
     * "full_cut": "0.00",
     * "explain": "六一活动期间",
     * "begin_time": "2020-06-12 00:00:00",
     * "end_time": "2020-06-28 23:59:59",
     * "cr_id": 31,
     * "sub_list": []
     * }
     * ],
     * "coupon_freight": [
     * {
     * "id": 10,
     * "name": "测试免邮券",
     * "type": 4,
     * "price": "0.00",
     * "full_cut": "0.00",
     * "explain": "商品免邮券",
     * "begin_time": "2020-06-12 00:00:00",
     * "end_time": "2020-06-28 23:59:59",
     * "cr_id": 35,
     * "sub_list": []
     * }
     * ]
     * },
     * "used_address": {
     * "id": 2814,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 0,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * }
     * }
     * }
     */
    public function prepareCreateGroupBuyOrder(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        if (0) {
            $params['sku'] = '1612728266';
            $params['goods_id'] = 91;
            $params['buy_num'] = intval($request->input('buy_num', 2));
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = 1;
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 2814);
            $params['os_type'] = 1;
            $params['buy_type'] = 1; //1开团 2参团
            $params['group_key'] = '';
        } else {
            $params['sku'] = $request->input('sku', '');
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = $request->input('post_type', 0);
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 0);
            $params['os_type'] = $request->input('os_type', 0);
            $params['buy_type'] = $request->input('buy_type', 0);
            $params['group_key'] = $request->input('group_key', '');
        }

        $model = new MallOrderGroupBuy();
        $data = $model->prepareCreateGroupBuyOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团订单下单
     * @api {post} /api/v4/mall/create_group_buy_order 拼团订单下单
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/create_group_buy_order
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/create_group_buy_order
     * @apiDescription 拼团订单下单
     * @apiParam {string} sku  sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)
     * @apiParam {string} goods_id 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} buy_num 如果是购物车则不用传,直接购买必须传
     * @apiParam {string} inviter 推客id,没有0
     * @apiParam {number=1,2} post_type 物流方式(1邮寄2自提)
     * @apiParam {number} coupon_freight_id 免邮券id,没有0
     * @apiParam {number} address_id 选择的地址id
     * @apiParam {number=1,2,3} os_type 1安卓2苹果3微信
     * @apiParam {buy_type=1,2} buy_type 1开团 2参团
     * @apiParam {number} group_key 如果是参团,需要传
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "sku":"1612728266",
     * "goods_id":209,
     * "buy_num":1,
     * "inviter":211172,
     * "post_type":1,
     * "coupon_goods_id":0,
     * "coupon_freight_id":0,
     * "address_id":2814,
     * "os_type":1,
     * "buy_type":1,
     * "group_key":1
     * }
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "order_id": 9555,
     * "ordernum": "2006230016893460198201",
     * "group_key": "2006230016893460198117"
     * }
     * }
     */
    public function createGroupBuyOrder(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        if (0) {
            $params['sku'] = '1904221194';
            $params['goods_id'] = 160;
            $params['buy_num'] = intval($request->input('buy_num', 2));
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = 1;
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 2814);
            $params['os_type'] = 1;
            $params['buy_type'] = 1; //1开团 2参团
            $params['group_key'] = '';
        } else {
            $params['sku'] = $request->input('sku', '');
            $params['goods_id'] = $request->input('goods_id', 0);
            $params['buy_num'] = $request->input('buy_num', 0);
            $params['inviter'] = $request->input('inviter', 0);
            $params['post_type'] = $request->input('post_type', 0);
            $params['coupon_goods_id'] = $request->input('coupon_goods_id', 0);
            $params['coupon_freight_id'] = $request->input('coupon_freight_id', 0);
            $params['address_id'] = $request->input('address_id', 0);
            $params['os_type'] = $request->input('os_type', 0);
            $params['buy_type'] = $request->input('buy_type', 0);
            $params['group_key'] = $request->input('group_key', '');
        }

        $model = new MallOrderGroupBuy();
        $data = $model->createGroupBuyOrder($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 秒杀订单支付未成功处理
     * @api {post} /api/v4/mall/flash_sale_pay_fail 秒杀订单支付未成功处理
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/flash_sale_pay_fail
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/flash_sale_pay_fail
     * @apiDescription 秒杀订单支付未成功处理
     * @apiParam {string} order_id 订单id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "code": true,
     * "msg": "成功"
     * }
     * }
     */
    public function flashSalePayFail(Request $request)
    {
        $m = new MallOrder();
        $m->orderPaySuccess([]);

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $order_id = $request->input('order_id', 0);
        if (empty($order_id)) {
            return $this->error(0, '参数错误');
        }

        $model = new MallOrderFlashSale();
        $data = $model->flashSalePayFail($order_id, $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团队伍信息
     * @api {get} /api/v4/goods/group_buy_team_list 拼团队伍信息
     * @apiVersion 1.0.0
     * @apiName /api/v4/goods/group_buy_team_list
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_team_list
     * @apiDescription 拼团队伍信息
     * @apiParam {number} group_buy_id 拼团id
     * @apiParam {numer} [group_key] 拼团队伍标识
     * @apiParam {number=1,2} [flag] 1只返回两条 2全返
     * @apiSuccess {string} group_name group_buy_id
     * @apiSuccess {string} created_at 创建时间
     * @apiSuccess {string} user_id 队长id
     * @apiSuccess {string} end_at 队伍失效时间
     * @apiSuccess {string} nickname 队长昵称
     * @apiSuccess {string} headimg 头像
     * @apiSuccess {string} group_num 组队需要人数
     * @apiSuccess {string} group_key 队伍标似
     * @apiSuccess {string} order_count 队伍已有人数
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 5,
     * "group_name": "111",
     * "order_id": 9560,
     * "created_at": "2020-06-28 18:13:52",
     * "user_id": 168934,
     * "is_success": 0,
     * "success_at": null,
     * "begin_at": "2020-06-28 18:13:52",
     * "end_at": "2020-07-28 18:14:59",
     * "nickname": "chandler",
     * "headimg": null,
     * "group_num": 4,
     * "group_key": "2006280016893465633736",
     * "order_count": 1
     * }
     * ]
     * }
     */
    public function groupByTeamList(Request $request)
    {
        $params = $request->input();
        $model = new MallOrderGroupBuy();

        $data = $model->groupByTeamList($params, $this->user);

        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团滚动信息
     * @api {get} /api/v4/goods/group_buy_scrollbar 拼团滚动信息
     * @apiVersion 1.0.0
     * @apiName /api/v4/goods/group_buy_scrollbar
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_scrollbar
     * @apiDescription 拼团滚动信息
     * @apiParam {number} group_buy_id 拼团id
     *
     * @apiSuccess {string} user_id 用户id
     * @apiSuccess {string} headimg 头像
     * @apiSuccess {string} nickname 昵称
     * @apiSuccess {string} explain 说明
     * @apiSuccess {string} created_at 订单时间
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "user_id": 168934,
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
     * "nickname": "chandler",
     * "created_at": "2020-06-23 16:16:24",
     * "is_captain": 1,
     * "is_success": 0,
     * "explain": "发起拼团"
     * }
     * ]
     * }
     */
    public function gbScrollbar(Request $request)
    {
        $group_buy_id = $request->input('group_buy_id', 0);
        $size = $request->input('size', 10);
        $model = new MallOrderGroupBuy();
        $data = $model->gbScrollbar($group_buy_id, $size);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 普通和秒杀订单的列表
     * @api {get} /api/v4/mall/order_list 普通和秒杀订单的列表
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/order_list
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/order_list
     * @apiDescription 普通和秒杀订单的列表
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     * @apiParam {number} status 订单状态(全部0,待付款1,待发货10,待签收20,已完成30,已取消99)
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "status":0
     * }
     *
     * @apiSuccess {number} id 订单id
     * @apiSuccess {string} ordernum 订单编号
     * @apiSuccess {string} status 状态
     * @apiSuccess {string} price 订单金额
     * @apiSuccess {string} goods_count 商品数量
     * @apiSuccess {string[]} order_details 订单商品列表
     *
     * @apiSuccess {string} order_details.num 购买数量
     * @apiSuccess {string[]} order_details.goods_info 商品信息
     * @apiSuccess {string} order_details.goods_info.name 商品名称
     * @apiSuccess {string} order_details.goods_info.subtitle 商品说明
     * @apiSuccess {string} order_details.goods_info.picture 商品图片
     * @apiSuccess {string} order_details.goods_info.id 商品id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 9526,
     * "ordernum": "2006180016893463957101",
     * "status": 1,
     * "price": "741.27",
     * "goods_count": 7,
     * "order_details": [
     * {
     * "status": 0,
     * "goods_id": 91,
     * "num": 2,
     * "order_id": 9526,
     * "goods_info": {
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "id": 91
     * }
     * }
     * ]
     * }
     * ]
     * }
     */
    function list(Request $request)
    {
        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 10;

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $model = new MallOrder();
        $data = $model->userOrderList($params, $this->user);

        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 普通和秒杀订单的详情
     * @api {get} /api/v4/mall/order_info 普通和秒杀订单的详情
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/order_info
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/order_info
     * @apiDescription 普通和秒杀订单的详情
     * @apiParam {string} ordernum 订单编号
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "ordernum":'2006190016893457221111'
     * }
     *
     * @apiSuccess {number} id 订单id
     * @apiSuccess {string} ordernum 订单编号
     * @apiSuccess {string} dead_time 未支付的失效时间
     * @apiSuccess {number} status 订单状态(同列表)
     * @apiSuccess {string} message 留言
     * @apiSuccess {string[]} address_history 收货地址
     * @apiSuccess {string} address_history.name 收货人
     * @apiSuccess {string} address_history.phone 电话
     * @apiSuccess {string} address_history.details 详情
     * @apiSuccess {string} address_history.province_name 省
     * @apiSuccess {string} address_history.city_name 市
     * @apiSuccess {string} address_history.area_name 区
     *
     * @apiSuccess {string[]} order_child 商品列表(按物流分组)
     * @apiSuccess {string} order_child.status  1:已发货 2:已签收
     * @apiSuccess {string} order_child.express_id 物流公司id
     * @apiSuccess {string} order_child.express_num 物流单号
     * @apiSuccess {string[]} order_child.order_details 商品详情
     * @apiSuccess {string} order_child.order_details.goods_id 商品id
     * @apiSuccess {string} order_child.order_details.num 购买数量
     * @apiSuccess {string} order_child.order_details.sku_value 规格信息
     * @apiSuccess {string} order_child.order_details.name 商品名称
     * @apiSuccess {string} order_child.order_details.subtitle 副标题
     * @apiSuccess {string} order_child.order_details.picture 图片
     * @apiSuccess {string} order_child.order_details.price 购买单价
     * @apiSuccess {string} order_child.order_details.original_price 购买原价
     *
     * @apiSuccess {string[]} price_info 价格
     * @apiSuccess {string} price_info.cost_price 总价格
     * @apiSuccess {string} price_info.freight 运费
     * @apiSuccess {string} price_info.vip_cut 权益立减
     * @apiSuccess {string} price_info.coupon_money 优惠券金额
     * @apiSuccess {string} price_info.special_price_cut 活动立减
     * @apiSuccess {string} price_info.pay_time 支付时间
     * @apiSuccess {string} price_info.pay_type 支付渠道(1微信端 2app微信 3app支付宝 4ios)
     * @apiSuccess {string} price_info.price 订单金额
     *
     * @apiSuccess {string[]} bill 发票
     * @apiSuccess {string} bill.bill_type   0为不开发票 1个人 2公司
     * @apiSuccess {string} bill.bill_title  发票抬头
     * @apiSuccess {string} bill.bill_number 纳税人识别号
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "id": 9530,
     * "ordernum": "2006190016893457221111",
     * "dead_time": null,
     * "status": 10,
     * "address_history": {
     * "id": 2814,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 0,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * },
     * "messages": "",
     * "post_type": 1,
     * "goods_count": 7,
     * "order_child": [
     * {
     * "status": 1,
     * "order_id": 9530,
     * "express_id": 1,
     * "express_num": "1111111",
     * "order_detail_id": [
     * "10335",
     * "10336",
     * "10337"
     * ],
     * "order_details": [
     * {
     * "goods_id": 91,
     * "num": 2,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "price": "9.70",
     * "original_price": "379.00",
     * "name": "AR立体浮雕星座地球仪",
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "details_id": 10335
     * },
     * {
     * "goods_id": 98,
     * "num": 1,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "王琨专栏学习套装"
     * }
     * ],
     * "price": "254.15",
     * "original_price": "399.00",
     * "name": "王琨专栏学习套装",
     * "picture": "/wechat/mall/goods/8885_1545795771.png",
     * "subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
     * "details_id": 10336
     * }
     * ]
     * },
     * {
     * "status": 1,
     * "order_id": 9530,
     * "express_id": 1,
     * "express_num": "2222222",
     * "order_detail_id": [
     * "10338"
     * ],
     * "order_details": [
     * {
     * "goods_id": 209,
     * "num": 2,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "儿童财商绘本(全10册)"
     * }
     * ],
     * "price": "134.64",
     * "original_price": "180.00",
     * "name": "儿童财商绘本(全10册)",
     * "picture": "/wechat/mall/goods/625_1544239955.png",
     * "subtitle": "帮助孩子建立正确的金钱观念 从容面对金钱问题",
     * "details_id": 10338
     * }
     * ]
     * }
     * ],
     * "price_info": {
     * "cost_price": "1789.00",
     * "freight": "13.00",
     * "vip_cut": "304.13",
     * "coupon_money": "0.00",
     * "special_price_cut": "738.60",
     * "pay_time": null,
     * "pay_type": 0,
     * "price": "759.27"
     * },
     * "bill_info": {
     * "bill_type": 0,
     * "bill_title": "",
     * "bill_number": "",
     * "bill_format": 0
     * }
     * }
     * }
     */
    public function orderInfo(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $ordernum = $request->input('ordernum', 0);
        $model = new MallOrder();
        $data = $model->orderInfo($this->user['id'], $ordernum);

        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团订单的列表
     * @api {get} /api/v4/mall/group_buy_order_list 拼团订单的列表
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/group_buy_order_list
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/group_buy_order_list
     * @apiDescription 拼团订单的列表
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     * @apiParam {number} status 订单状态(全部0,待付款1,待发货10,待签收20,已完成30,已取消99,拼团中95)
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "status":0
     * }
     *
     * @apiSuccess {number} id 订单id
     * @apiSuccess {string} ordernum 订单编号
     * @apiSuccess {string} status 状态
     * @apiSuccess {string} price 订单金额
     * @apiSuccess {string} goods_count 商品数量
     * @apiSuccess {string[]} order_details 订单商品列表
     *
     * @apiSuccess {string} order_details.num 购买数量
     * @apiSuccess {string[]} order_details.goods_info 商品信息
     * @apiSuccess {string} order_details.goods_info.name 商品名称
     * @apiSuccess {string} order_details.goods_info.subtitle 商品说明
     * @apiSuccess {string} order_details.goods_info.picture 商品图片
     * @apiSuccess {string} order_details.goods_info.id 商品id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 9526,
     * "ordernum": "2006180016893463957101",
     * "status": 1,
     * "price": "741.27",
     * "goods_count": 7,
     * "order_details": [
     * {
     * "status": 0,
     * "goods_id": 91,
     * "num": 2,
     * "order_id": 9526,
     * "goods_info": {
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "id": 91
     * }
     * }
     * ]
     * }
     * ]
     * }
     */
    public function listOfGroupBuy(Request $request)
    {
        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 10;

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $model = new MallOrderGroupBuy();
        $data = $model->userOrderList($params, $this->user);

        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 拼团订单详情
     * @api {get} /api/v4/mall/group_buy_order_info 拼团订单详情
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/group_buy_order_info
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/group_buy_order_info
     * @apiDescription 拼团订单详情
     * @apiParam {string} ordernum 订单编号
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "ordernum":'2006190016893457221111'
     * }
     *
     * @apiSuccess {number} id 订单id
     * @apiSuccess {string} ordernum 订单编号
     * @apiSuccess {string} dead_time 未支付的失效时间
     * @apiSuccess {number} status 订单状态(同列表)
     * @apiSuccess {string} message 留言
     * @apiSuccess {string[]} address_history 收货地址
     * @apiSuccess {string} address_history.name 收货人
     * @apiSuccess {string} address_history.phone 电话
     * @apiSuccess {string} address_history.details 详情
     * @apiSuccess {string} address_history.province_name 省
     * @apiSuccess {string} address_history.city_name 市
     * @apiSuccess {string} address_history.area_name 区
     *
     * @apiSuccess {string[]} order_child 商品列表(按物流分组)
     * @apiSuccess {string} order_child.status  1:已发货 2:已签收
     * @apiSuccess {string} order_child.express_id 物流公司id
     * @apiSuccess {string} order_child.express_num 物流单号
     * @apiSuccess {string[]} order_child.order_details 商品详情
     * @apiSuccess {string} order_child.order_details.goods_id 商品id
     * @apiSuccess {string} order_child.order_details.num 购买数量
     * @apiSuccess {string} order_child.order_details.sku_value 规格信息
     * @apiSuccess {string} order_child.order_details.name 商品名称
     * @apiSuccess {string} order_child.order_details.subtitle 副标题
     * @apiSuccess {string} order_child.order_details.picture 图片
     * @apiSuccess {string} order_child.order_details.price 购买单价
     * @apiSuccess {string} order_child.order_details.original_price 购买原价
     *
     * @apiSuccess {string[]} price_info 价格
     * @apiSuccess {string} price_info.cost_price 总价格
     * @apiSuccess {string} price_info.freight 运费
     * @apiSuccess {string} price_info.vip_cut 权益立减
     * @apiSuccess {string} price_info.coupon_money 优惠券金额
     * @apiSuccess {string} price_info.special_price_cut 活动立减
     * @apiSuccess {string} price_info.pay_time 支付时间
     * @apiSuccess {string} price_info.pay_type 支付渠道(1微信端 2app微信 3app支付宝 4ios)
     * @apiSuccess {string} price_info.price 订单金额
     *
     * @apiSuccess {string[]} bill 发票
     * @apiSuccess {string} bill.bill_type   0为不开发票 1个人 2公司
     * @apiSuccess {string} bill.bill_title  发票抬头
     * @apiSuccess {string} bill.bill_number 纳税人识别号
     *
     * @apiSuccess {string[]} team_user_list 队友列表
     * @apiSuccess {string} team_user_list.user_id  用户id
     * @apiSuccess {string} team_user_list.nickname 昵称
     * @apiSuccess {string} team_user_list.headimg  头像
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "id": 9530,
     * "ordernum": "2006190016893457221111",
     * "dead_time": null,
     * "status": 10,
     * "address_history": {
     * "id": 2814,
     * "name": "sfas",
     * "phone": "18624078563",
     * "details": "sdkfjsljfl1ao",
     * "is_default": 0,
     * "province": 210000,
     * "city": 210100,
     * "area": 210102,
     * "province_name": "辽宁",
     * "city_name": "沈阳",
     * "area_name": "和平区"
     * },
     * "messages": "",
     * "post_type": 1,
     * "goods_count": 7,
     * "order_child": [
     * {
     * "status": 1,
     * "order_id": 9530,
     * "express_id": 1,
     * "express_num": "1111111",
     * "order_detail_id": [
     * "10335",
     * "10336",
     * "10337"
     * ],
     * "order_details": [
     * {
     * "goods_id": 91,
     * "num": 2,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ],
     * "price": "9.70",
     * "original_price": "379.00",
     * "name": "AR立体浮雕星座地球仪",
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "details_id": 10335
     * },
     * {
     * "goods_id": 98,
     * "num": 1,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "王琨专栏学习套装"
     * }
     * ],
     * "price": "254.15",
     * "original_price": "399.00",
     * "name": "王琨专栏学习套装",
     * "picture": "/wechat/mall/goods/8885_1545795771.png",
     * "subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
     * "details_id": 10336
     * }
     * ]
     * },
     * {
     * "status": 1,
     * "order_id": 9530,
     * "express_id": 1,
     * "express_num": "2222222",
     * "order_detail_id": [
     * "10338"
     * ],
     * "order_details": [
     * {
     * "goods_id": 209,
     * "num": 2,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "儿童财商绘本(全10册)"
     * }
     * ],
     * "price": "134.64",
     * "original_price": "180.00",
     * "name": "儿童财商绘本(全10册)",
     * "picture": "/wechat/mall/goods/625_1544239955.png",
     * "subtitle": "帮助孩子建立正确的金钱观念 从容面对金钱问题",
     * "details_id": 10338
     * }
     * ]
     * }
     * ],
     * "price_info": {
     * "cost_price": "1789.00",
     * "freight": "13.00",
     * "vip_cut": "304.13",
     * "coupon_money": "0.00",
     * "special_price_cut": "738.60",
     * "pay_time": null,
     * "pay_type": 0,
     * "price": "759.27"
     * },
     * "bill_info": {
     * "bill_type": 0,
     * "bill_title": "",
     * "bill_number": "",
     * "bill_format": 0
     * },
     * "team_user_list": [
     * {
     * "id": 1,
     * "user_id": 168934,
     * "nickname": "chandler",
     * "headimg": null,
     * "is_captain": 1
     * }
     * ]
     * }
     * }
     */
    public function groupBuyOrderInfo(Request $request)
    {

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $ordernum = $request->input('ordernum', 0);
        $model = new MallOrderGroupBuy();
        $data = $model->orderInfo($this->user['id'], $ordernum);

        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 修改订单状态
     * @api {put} /api/v4/mall/status_change 修改订单状态(取消,删除,确认收货)
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/status_change
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/status_change
     * @apiDescription 修改订单状态
     * @apiParam {string} id 订单id
     * @apiParam {string=stop,del,receipt} flag 标记
     */
    public function statusChange(Request $request)
    {

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $flag = $request->input('flag', '');
        if (empty($flag)) {
            return $this->error(0, '参数错误');
        } else {
            $flag = strtolower($flag);
        }
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error(0, '参数错误');
        }
        $model = new MallOrder();
        $data = $model->statusChange($id, $flag, $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 商品评论列表
     * @api {get} /api/v4/mall/comment_list 商品评论列表
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/comment_list
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/comment_list
     * @apiDescription 未评论商品列表
     * @apiParam {number=1,2,3} flag 标记(1已评价,未评价,3全部)
     * @apiParam {number} [order_id] 如果按订单筛选,传订单id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "order_id": 9527,
     * "ordernum": "2006190016893436005551",
     * "order_detail_id": 10327,
     * "name": "AR立体浮雕星座地球仪",
     * "subtitle": "高清生动准确的星座秘密等你来发现",
     * "comment_id": 0,
     * "sku_value": [
     * {
     * "key_name": "规格",
     * "value_name": "AR立体浮雕星座地球仪"
     * }
     * ]
     * }
     * ]
     * }
     */
    public function commentList(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $model = new MallOrder();
        $data = $model->commentList($this->user['id'], $request->input());
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 评价
     * @api {post} /api/v4/mall/sub_comment 评价
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/sub_comment
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/sub_comment
     * @apiDescription 评价
     * @apiParam {number} order_detail_id id
     * @apiParam {number=1,2,3,4,5} star 星级
     * @apiParam {string} picture 图片,多张用逗号隔开
     * @apiParam {string} issue_type 原因,多个用逗号隔开(1,2,3)
     * @apiParam {string} content 评价内容
     */
    public function subComment(Request $request)
    {

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallOrder();
        $data = $model->subComment($params, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 查看评价
     * @api {get} /api/v4/mall/get_comment 查看评价
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/get_comment
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/get_comment
     * @apiDescription 查看评价
     * @apiParam {number} comment_id id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "id": 973,
     * "content": "",
     * "picture": [],
     * "star": 5,
     * "status": 1,
     * "issue_type": []
     * }
     * }
     */
    public function getComment(Request $request)
    {

        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $comment_id = $request->input('comment_id', 0);
        $model = new MallComment();
        $data = $model->getComment($comment_id, $this->user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 评价原因列表
     * @api {get} /api/v4/mall/comment_issue_list 评价原因列表
     * @apiVersion 1.0.0
     * @apiName /api/v4/mall/comment_issue_list
     * @apiGroup MallOrder
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/mall/comment_issue_list
     * @apiDescription 评价原因列表
     * @apiParam {number} comment_id id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 1,
     * "val": "商品问题"
     * },
     * {
     * "id": 2,
     * "val": "客服问题"
     * },
     * {
     * "id": 3,
     * "val": "物流问题"
     * },
     * {
     * "id": 4,
     * "val": "其他问题"
     * }
     * ]
     * }
     */
    public function commentIssueList()
    {
        $res = \App\Models\ConfigModel::getData(13);
        return $this->success(json_decode($res));
    }

}
