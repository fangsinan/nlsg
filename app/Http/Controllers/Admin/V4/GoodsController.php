<?php


namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Servers\MallCommentServers;
use Illuminate\Http\Request;
use App\Servers\GoodsServers;

class GoodsController extends Controller
{
    /**
     * 添加商品
     * @api {post} /api/admin_v4/goods/add 添加商品
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/add
     * @apiGroup  后台-商品管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/add
     * @apiDescription 添加商品
     * @apiParam {number} category_id 分类id
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "category_id": 1,
     * "name": "手机紧身的繁了",
     * "subtitle": "一个还行的手机",
     * "picture": "/phone/1.jpg",
     * "freight_id": 1,
     * "original_price": 9999,
     * "price": 999,
     * "keywords": "手机,智能,安卓",
     * "content": "<p>图文简介啊发撒发撒地方</p>",
     * "status": 1,
     * "picture_list": [
     * {
     * "is_video": 1,
     * "url": "/phone/video/1.mp4",
     * "is_main": 1,
     * "cover_img": "/phone/1mp4.jpg"
     * },
     * {
     * "is_video": 1,
     * "url": "/phone/video/2.mp4",
     * "is_main": 0,
     * "cover_img": "/phone/2mp4.jpg"
     * },
     * {
     * "is_video": 0,
     * "url": "/phone/4.jpg",
     * "is_main": 1
     * },
     * {
     * "is_video": 0,
     * "url": "/phone/5.jpg",
     * "is_main": 0
     * }
     * ],
     * "tos": [
     * 1,
     * 2,
     * 3
     * ],
     * "sku_list": [
     * {
     * "picture": "/phone/hong.jpg",
     * "original_price": "9999",
     * "price": "999",
     * "cost": 6.6,
     * "promotion_cost": 0,
     * "stock": 100,
     * "warning_stock": 10,
     * "status": 1,
     * "weight": 250,
     * "volume": 100,
     * "erp_enterprise_code": "",
     * "erp_goods_code": "",
     * "value_list": [
     * {
     * "key_name": "颜色",
     * "value_name": "红"
     * },
     * {
     * "key_name": "材质",
     * "value_name": "铁"
     * }
     * ]
     * },
     * {
     * "picture": "/phone/huang.jpg",
     * "original_price": "9999",
     * "price": 888,
     * "cost": 7,
     * "promotion_cost": 0,
     * "stock": 100,
     * "warning_stock": 5,
     * "status": 1,
     * "weight": 250,
     * "volume": 120,
     * "erp_enterprise_code": "",
     * "erp_goods_code": "",
     * "value_list": [
     * {
     * "key_name": "颜色",
     * "value_name": "黄"
     * },
     * {
     * "key_name": "材质",
     * "value_name": "木头"
     * }
     * ]
     * }
     * ]
     * }
     *
     * @apiSuccess {number} id id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data":{}
     * }
     */
    public function add(Request $request)
    {
        $servers = new GoodsServers();
        $data = $servers->add($request->input());
        return $this->getRes($data);
    }

    /**
     * 商品列表
     * @api {post} /api/admin_v4/goods/list 商品列表
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/list
     * @apiGroup  后台-商品管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/list
     * @apiDescription 商品列表
     */
    public function list(Request $request)
    {
        $servers = new GoodsServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * 商品分类列表
     * @api {post} /api/admin_v4/goods/category_list 商品分类列表
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/category_list
     * @apiGroup  后台-商品管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/category_list
     * @apiDescription 商品分类列表
     */
    public function categoryList(Request $request)
    {
        $servers = new GoodsServers();
        $data = $servers->categoryList();
        return $this->getRes($data);
    }



    public function tempTools(Request $request)
    {
//        $type = $request->input('type',0);
//        $id = $request->input('id',0);
//        if ($type && $id){
//            switch ($type){
//                case 'live_pass':
//                    $check = Live::whereId($id)->first();
//                    if($check){
//                      $check->status = 4;
//                      $check->check_time = date('Y-m-d H:i:s');
//                      $check->save();
//                    }
//            }
//        }
    }
}

