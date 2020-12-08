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
     * @apiParam {number=1,2,4} type 类型(1优惠4拼团)
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
     *
     * @apiParam {number} team_id 时间段id(1(9:00-12:59),2(13:00-18:59),3(19:00 - 20:59),4(21:00-次日8:59))
     * @apiParam {string} date 日期(2020-11-11)
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string} [group_name] 如果编辑的时候,需要额外传它
     * @apiParam {string[]} list sku列表
     * @apiParam {string} list.goods_id 商品id
     * @apiParam {string} list.goods_price 秒杀价格
     * @apiParam {string[]} list.list
     * @apiParam {string} list.list.sku_number 规格
     * @apiParam {string} list.list.sku_price 规格收加
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "team_id": 4,
     * "date": "2020-11-11",
     * "status": 1,
     * "group_name":"48roQ1604475214",
     * "list": [
     * {
     * "goods_id": 57,
     * "goods_price": 1.4,
     * "list": [
     * {
     * "sku_number": 1825350558,
     * "sku_price": 1.4
     * }
     * ]
     * },
     * {
     * "goods_id": 330,
     * "goods_price": 0.67,
     * "list": [
     * {
     * "sku_number": 1835215184,
     * "sku_price": 0.67
     * },
     * {
     * "sku_number": 1607088243,
     * "sku_price": 0.68
     * },
     * {
     * "sku_number": 1835215184,
     * "sku_price": 2.1
     * }
     * ]
     * }
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
        $servers = new SpecialPriceServers();
        if (0) {
            //临时,批量添加测试数
            $temp_add_data = $servers->addFlashSaleNewTemp();
            //return $this->getRes($temp_add_data);
            foreach ($temp_add_data as $v) {
                $temp_res = $servers->addFlashSaleNew($v);
                var_dump($temp_res);
            }
        } else {
            $data = $servers->addFlashSaleNew($request->input());
            return $this->getRes($data);
        }
    }

    /**
     * 秒杀的列表
     * @api {get} /api/admin_v4/special_price/flash_sale_list 秒杀的列表
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/special_price/flash_sale_list
     * @apiGroup 后台管理-商品价格设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/flash_sale_list
     * @apiDescription 秒杀的列表
     * @apiParam {string} goods_name 商品名称
     * @apiParam {string} begin_time 开始时间
     * @apiParam {string} end_time 结束时间
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string} group_name 获取详情和编辑时用(id没用)
     **/
    public function flashSaleList(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->flashSaleList($request->input());
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
