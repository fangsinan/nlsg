<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MallOrder;

class MallOrderController extends Controller {

    /**
     * 预下单
     * @api {post} /api/V4/mall/prepare_create_order 普通订单预下单
     * @apiVersion 1.0.0
     * @apiName /api/V4/mall/prepare_create_order
     * @apiGroup MallOrder
     * @apiSampleRequest /api/V4/mall/prepare_create_order
     * @apiDescription 普通订单预下单
     * @apiParam {number=0,1} from_cart 下单方式(1:购物车下单  2:立即购买
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
     * 
     * @apiSuccess {number} name 名称
     * @apiSuccess {number} subtitle 副标题
     * @apiSuccessExample {json} Request-Example:
      {
      "sku_list": [
      {
      "name": "AR立体浮雕星座地球仪",
      "subtitle": "高清生动准确的星座秘密等你来发现",
      "picture": "/wechat/mall/mall/goods/2224_1520841037.png",
      "sku_value_list": [
      {
      "key_name": "规格",
      "value_name": "AR立体浮雕星座地球仪"
      }
      ],
      "num": 2,
      "original_price": "379.00",
      "price": "9.70"
      },
      {
      "name": "王琨专栏学习套装",
      "subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
      "picture": "/wechat/mall/goods/8873_1545796221.png",
      "sku_value_list": [
      {
      "key_name": "规格",
      "value_name": "王琨专栏学习套装"
      }
      ],
      "num": 1,
      "original_price": "399.00",
      "price": "254.15"
      }
      ],
      "price_list": {
      "all_original_price": "1789.00",
      "all_price": "746.27",
      "freight_money": "13.00",
      "vip_cut_money": "304.13",
      "sp_cut_money": "738.60",
      "coupon_money": 0,
      "freight_free_flag": false,
      "order_price": "759.27"
      },
      "address_list": [
      {
      "id": 2815,
      "name": "sfas",
      "phone": "18624078563",
      "details": "sdkfjsljfl1ao",
      "is_default": 1,
      "province": 210000,
      "city": 210100,
      "area": 210102,
      "province_name": "辽宁",
      "city_name": "沈阳",
      "area_name": "和平区"
      }
      ],
      "coupon_list": {
      "coupon_goods": [
      {
      "id": 7,
      "name": "5元优惠券(六一专享)",
      "type": 3,
      "price": "5.00",
      "full_cut": "0.00",
      "explain": "六一活动期间",
      "begin_time": "2020-06-12 00:00:00",
      "end_time": "2020-06-19 23:59:59"
      }
      ],
      "coupon_freight": [
      {
      "id": 10,
      "name": "测试免邮券",
      "type": 4,
      "price": "0.00",
      "full_cut": "0.00",
      "explain": "商品免邮券",
      "begin_time": "2020-06-12 00:00:00",
      "end_time": "2020-06-22 23:59:59"
      }
      ]
      }
      }
     */
    public function prepareCreateOrder(Request $request) {

        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];

        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
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
        $data = $model->prepareCreateOrder($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 下单
     * @api {post} /api/V4/mall/create_order 普通订单下单
     * @apiVersion 1.0.0
     * @apiName /api/V4/mall/create_order
     * @apiGroup MallOrder
     * @apiSampleRequest /api/V4/mall/create_order
     * @apiDescription 普通订单下单(参数同预下单)
     * @apiParam {number=0,1} from_cart 下单方式(1:购物车下单  2:立即购买
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
     *  @apiSuccessExample {json} Request-Example:
      {
      "code": 200,
      "msg": "成功",
      "data": {
      "order_id": 9530,
      "ordernum": "2006190016893457221111"
      }
      }
     */
    public function createOrder(Request $request) {

        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];

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

        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new MallOrder();
        $data = $model->createOrder($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    
    
    
    //todo 秒杀订单预下单
    //todo 秒杀订单下单
    
    
    
    
    
    
    
    
    //todo 拼团订单预下单
    //todo 拼团订单下单
 
    
    
    
    //todo 订单详情
    //todo 取消订单
    //todo 确认收货
    //todo 删除订单
    //todo 评论
}
