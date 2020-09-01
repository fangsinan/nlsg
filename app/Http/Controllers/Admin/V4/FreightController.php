<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\FreightServers;
use Illuminate\Http\Request;

class FreightController extends Controller
{


    /**
     * 运费模板
     * @api {post} /api/admin_v4/freight/list 运费模板
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/freight/list
     * @apiGroup  后台-运费模板
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/freight/list
     * @apiDescription 运费模板
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     *
     * @apiSuccess {number} id id
     * @apiSuccess {number} type 类型
     * @apiSuccess {number} nane 名称
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598335384,
     * "data": {
     * "current_page": 1,
     * "data": [
     * {
     * "id": 14,
     * "type": 1,
     * "name": "北京发货3-件数"
     * }
     * ],
     * "first_page_url": "http://127.0.0.1:8000/api/admin_v4/freight/list?page=1",
     * "from": 1,
     * "last_page": 1,
     * "last_page_url": "http://127.0.0.1:8000/api/admin_v4/freight/list?page=1",
     * "next_page_url": null,
     * "path": "http://127.0.0.1:8000/api/admin_v4/freight/list",
     * "per_page": 10,
     * "prev_page_url": null,
     * "to": 3,
     * "total": 3
     * }
     * }
     */
    public function list(Request $request)
    {
        $servers = new FreightServers();
        $data = $servers->list($request->input(), 1);
        return $this->getRes($data);
    }


    /**
     * 退货和自提地址
     * @api {post} /api/admin_v4/freight/shop_list 退货和自提地址
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/freight/shop_list
     * @apiGroup  后台-运费模板
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/freight/shop_list
     * @apiDescription 退货和自提地址
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     * @apiParam {number=2,3} type 类型(2:自提点 3:退货点)
     *
     * @apiSuccess {number} id id
     * @apiSuccess {number} type 类型
     * @apiSuccess {number} nane 名称
     * @apiSuccess {string} admin_name 管理员
     * @apiSuccess {string} admin_phone 管理员电话
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598335514,
     * "data": {
     * "current_page": 1,
     * "data": [
     * {
     * "id": 9,
     * "type": 2,
     * "name": "自提点2",
     * "admin_name": "李四",
     * "admin_phone": "112331",
     * "phone": "112331",
     * "province": 110000,
     * "city": 110105,
     * "area": 0,
     * "details": "朝阳路85号",
     * "start_time": "2020-06-15 17:50:54",
     * "end_time": "2037-01-01 00:00:00",
     * "province_name": "北京",
     * "city_name": "朝阳",
     * "area_name": ""
     * }
     * ]
     * }
     * }
     */
    public function shopList(Request $request)
    {
        $servers = new FreightServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }


    //todo 添加运费模板
    public function add()
    {

        $params = [
            'name'=>'模板测试1',
            'status'=>1,
            'count_type'=>1,
            'details'=>[
                //只允许一个type=1
                [
                    'name'=>'默认',
                    'type'=>1,
                    'start_price'=>10,
                    'status'=>1
                ],
                [
                    'name'=>'不包邮区域1',
                    'type'=>2,
                    'start_price'=>10,
                    'status'=>1
                ]
            ]
        ];


        return $this->getRes($params);
    }


    /**
     * 添加退货和自提地址
     * @api {post} /api/admin_v4/freight/add_shop 添加退货和自提地址
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/freight/add_shop
     * @apiGroup  后台-运费模板
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/freight/add_shop
     * @apiDescription 添加退货和自提地址
     * @apiParam {number=2,3} type 类型(2自提3退货)
     * @apiParam {number=1,2} status 状态(1上架2下架)
     * @apiParam {string} name 名称
     * @apiParam {string} admin_name 联系人
     * @apiParam {string} admin_phone 联系电话
     * @apiParam {string} details 详细地址
     * @apiParam {string} province 省区划码
     * @apiParam {string} city 市
     * @apiParam {string} area 区/县
     *
     * @apiParamExample {json} Request-Example:
     *
     * {
     * "type": 2,
     * "name": "台铭自提点",
     * "admin_name": "库锁",
     * "admin_phone": 1232456,
     * "details": "台铭国际企业花园",
     * "status": 1,
     * "province": 110000,
     * "city": 110105,
     * "area": 0
     * }
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1598335384,
     * "data": {
     * }
     * }
     */
    public function addShop(Request $request)
    {
        $servers = new FreightServers();
        $data = $servers->addShop($request->input());
        return $this->getRes($data);
    }

}
