<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\SpecialPriceServers;
use Illuminate\Http\Request;

class SpecialPriceController extends Controller
{

    /**
     * 列表
     * @api {get} /api/admin_v4/special_price/list 列表
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/special_price/list
     * @apiGroup 后台管理-商品价格设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/list
     * @apiDescription 列表
     * @apiParam {number=1,2,4} type 类型(1优惠2秒杀4拼团)
     * @apiParam {string} goods_name 商品名称
     * @apiParam {string} begin_time 开始时间
     * @apiParam {string} end_time 结束时间
     * @apiParam {number=1,2} status 状态(1上架2下架)
     **/
    public function list(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * 添加秒杀活动
     * @api {post} /api/admin_v4/special_price/add_flash_sale 添加秒杀活动
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/special_price/add_flash_sale
     * @apiGroup 后台管理-商品价格设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_flash_sale
     * @apiDescription 添加秒杀活动
     * @apiParam {number} goods_id 商品id
     * @apiParam {number=2} type 类型(固定2)
     * @apiParam {number} goods_price 商品价格
     * @apiParam {number} [goods_original_price] 商品原价(可不传)
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string[]} list sku列表
     * @apiParam {string} list.sku_number sku
     * @apiParam {string} list.sku_price sku秒杀价格
     * @apiParam {string[]} time_list 时间列表(秒杀时间端设置,数组,多条,时间范围)
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "goods_id": 91,
     * "type": 2,
     * "goods_price": 9.1,
     * "list": [
     * {
     * "sku_number": 1612728266,
     * "sku_price": 9.1
     * }
     * ],
     * "status":1,
     * "time_list": [
     * "2020-09-01 15:00:00,2020-08-01 15:04:59",
     * "2020-09-02 15:00:00,2020-08-02 15:04:59",
     * "2020-09-03 15:00:00,2020-08-03 15:04:59",
     * "2020-09-05 15:00:00,2020-08-05 15:04:59",
     * "2020-09-10 15:00:00,2020-08-10 15:04:59",
     * "2020-09-12 18:00:00,2020-08-12 18:04:59",
     * "2020-09-15 15:00:00,2020-08-15 15:01:59",
     * "2020-09-18 15:00:00,2020-08-18 15:04:59",
     * "2020-09-19 15:00:00,2020-08-19 15:04:59"
     * ]
     * }
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598421645,
     * "data": {
     * }
     * }
     **/
    public function addFlashSale(Request $request)
    {
        $params = $request->input();
        if (($params['type'] ?? 0) != 2) {
            $data = ['code' => false, 'msg' => 'type错误'];
        } else {
            $servers = new SpecialPriceServers();
            $data = $servers->add($params);
        }
        return $this->getRes($data);
    }

    /**
     * 添加拼团
     * @api {post} /api/admin_v4/special_price/add_group_buy 添加拼团
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/special_price/add_group_buy
     * @apiGroup 后台管理-商品价格设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_group_buy
     * @apiDescription 添加拼团
     * @apiParam {number} goods_id 商品id
     * @apiParam {number=2} type 类型(固定4)
     * @apiParam {number} goods_price 商品价格
     * @apiParam {number} [goods_original_price] 商品原价(可不传)
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string[]} list sku列表
     * @apiParam {string} list.sku_number sku
     * @apiParam {string} list.group_price 拼团价格
     * @apiParam {string} list.group_num 成团人数
     * @apiParam {string} begin_time 开始时间
     * @apiParam {string} end_time 结束时间
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "goods_id": 91,
     * "type": 4,
     * "goods_price": 7.7,
     * "list": [
     * {
     * "sku_number": 1612728266,
     * "group_price": 7.7,
     * "group_num": 2
     * }
     * ],
     * "begin_time": "2020-09-15 14:00:00",
     * "end_time": "2020-10-01 23:59:59"
     * }
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598421645,
     * "data": {
     * }
     * }
     **/
    public function addGroupBuy(Request $request)
    {
        $params = $request->input();
        if (($params['type'] ?? 0) != 4) {
            $data = ['code' => false, 'msg' => 'type错误'];
        } else {
            $servers = new SpecialPriceServers();
            $data = $servers->add($params);
        }
        return $this->getRes($data);
    }

    /**
     * 添加优惠活动
     * @api {post} /api/admin_v4/special_price/add_normal 添加优惠活动
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/special_price/add_normal
     * @apiGroup 后台管理-商品价格设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_normal
     * @apiDescription 添加优惠活动
     * @apiParam {number} goods_id 商品id
     * @apiParam {number=1} type 类型(固定2)
     * @apiParam {number} goods_price 商品价格
     * @apiParam {number} [goods_original_price] 商品原价(可不传)
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string} begin_time 开始时间
     * @apiParam {string} end_time 结束时间
     * @apiParam {string[]} list sku列表
     * @apiParam {string} list.sku_number sku
     * @apiParam {number} list.sku_price 购买价格
     * @apiParam {number} list.sku_price_black 黑钻购买价格
     * @apiParam {number} list.sku_price_yellow  皇钻购买价格
     * @apiParam {number} list.sku_price_dealer 经销商购买价格
     * @apiParam {number=1,0} list.is_set_t_money 是否单独设置推客收益(1设2不设)
     * @apiParam {number} [list.t_money] 普通推客收益(is_set_t_money=1时传该值)
     * @apiParam {number} [list.t_money_black] 黑钻收益(is_set_t_money=1时传该值)
     * @apiParam {number} [list.t_money_yellow] 皇钻收益(is_set_t_money=1时传该值)
     * @apiParam {number} [list.t_money_dealer] 经销商收益(is_set_t_money=1时传该值)
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "goods_id": 91,
     * "type": 1,
     * "goods_price": 7.7,
     * "status": 1,
     * "list": [
     * {
     * "sku_number": 1612728266,
     * "sku_price": 11,
     * "sku_price_black": 12,
     * "sku_price_yellow": 13,
     * "sku_price_dealer": 14,
     * "is_set_t_money": 1,
     * "t_money": 1,
     * "t_money_black": 1.1,
     * "t_money_yellow": 1.2,
     * "t_money_dealer": 1.3
     * }
     * ],
     * "begin_time": "2020-09-15 14:00:00",
     * "end_time": "2020-10-01 23:59:59"
     * }
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598421645,
     * "data": {
     * }
     * }
     **/
    public function addNormal(Request $request)
    {
        $params = $request->input();
        if (($params['type'] ?? 0) != 1) {
            $data = ['code' => false, 'msg' => 'type错误'];
        } else {
            $servers = new SpecialPriceServers();
            $data = $servers->add($params);
        }
        return $this->getRes($data);
    }

    public function statusChange(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->statusChange($request->input());
        return $this->getRes($data);
    }
}
