<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\MallAddress;
use App\Models\FreightTemplate;

/**
 * Description of AddressController
 *
 * @author wangxh
 */
class AddressController extends Controller {

    /**
     * 行政区划表数据
     * @api {get} /api/V4/address/get_data 行政区划表数据
     * @apiVersion 4.0.0
     * @apiName /api/V4/address/get_data
     * @apiGroup  address
     * @apiSampleRequest /api/V4/address/get_data
     * @apiDescription 行政区划表数据
     * @apiSuccessExample {json} Request-Example:
     * 
      {
        "code": 200,
        "msg": "成功",
        "data": {
            {
                "id": 110000,
                "name": "北京",
                "pid": 0,
                "area_list": [
                    {
                        "id": 110101,
                        "name": "东城",
                        "pid": 110000,
                        "area_list": []
                    },
                    {
                        "id": 110102,
                        "name": "西城",
                        "pid": 110000,
                        "area_list": []
                    }
                ]
            }
        }
      }
     */
    public function getData() {
        $res = Area::getData();
        return $this->success($res);
    }

    /**
     * 添加,编辑
     * @api {post} /api/V4/address/create 添加,编辑
     * @apiVersion 1.0.0
     * @apiName /api/V4/address/create
     * @apiGroup address
     * @apiSampleRequest /api/V4/address/create
     * @apiDescription 添加,编辑
     * @apiParam {string} province 省
     * @apiParam {string} city 市
     * @apiParam {string} [area] 地区(如北京没有三级,可不传)
     * @apiParam {string} name 收货人名称
     * @apiParam {string} phone 收货人电话
     * @apiParam {string} details 详细地址
     * @apiParam {number=1,0} is_default 1:默认地址 0:普通
     *
     * @apiParamExample {json} Request-Example:
      {
      "province":210000,
      "city":210100,
      "area":210102,
      "name":"张三",
      "phone":"1111111111",
      "details":"数量的发掘Sofia1号",
      "is_default":1
      }
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "code": true,
      "msg": "成功"
      }
      }
     */
    public function create(Request $request) {
        $params = $request->input();
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $model = new MallAddress();
        $data = $model->create($params, $user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 收货地址列表
     * @api {get} /api/V4/address/get_list 收货地址列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/address/get_list
     * @apiGroup  address
     * @apiSampleRequest /api/V4/address/get_list
     * @apiDescription 收货地址列表,字段说明见创建接口
     * @apiSuccessExample {json} Request-Example:
     * 
      {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "id": 2812,
      "name": "李四",
      "phone": "18624078563",
      "details": "sdkfjsljfl1ao",
      "is_default": 1,
      "province": 210000,
      "city": 210100,
      "area": 210102,
      "province_name": "辽宁",
      "city_name": "沈阳",
      "area_name": "和平区"
      },
      {
      "id": 2816,
      "name": "sfas",
      "phone": "18624078563",
      "details": "sdkfjsljfl1ao",
      "is_default": 0,
      "province": 210000,
      "city": 210100,
      "area": 210102,
      "province_name": "辽宁",
      "city_name": "沈阳",
      "area_name": "和平区"
      }
      ]
      }
     */
    public function getList() {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $model = new MallAddress();
        $data = $model->getList($user['id']);
        return $this->success($data);
    }

    /**
     * 修改状态
     * @api {put} /api/V4/address/status_change 修改状态
     * @apiVersion 1.0.0
     * @apiName /api/V4/address/status_change
     * @apiGroup address
     * @apiSampleRequest /api/V4/address/status_change
     * @apiDescription 修改状态
     * @apiParam {string=default,nomal,del} flag 状态(默认,普通,删除)
     * @apiParam {number} id id
     *
     * @apiParamExample {json} Request-Example:
      {
      "flag":"default",
      "id":2815
      }
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "code": true,
      "msg": "成功"
      }
      }
     */
    public function statusChange(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
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
        $model = new MallAddress();
        $data = $model->statusChange($id, $flag, $user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 自提点和退货点列表
     * @api {get} /api/V4/address/list_of_shop 收货地址列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/address/list_of_shop
     * @apiGroup  address
     * @apiSampleRequest /api/V4/address/list_of_shop
     * @apiDescription 自提点和退货点列表
     * @apiParam {number=2,3} flag 2自提 3退货
     * @apiSuccessExample {json} Request-Example:
     * 
      {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "id": 10,
      "name": "退货点1",
      "admin_name": "啊哈哈",
      "admin_phone": "20349024",
      "province": 110000,
      "city": 110105,
      "area": 0,
      "details": "朝阳路85号",
      "province_name": "北京",
      "city_name": "朝阳",
      "area_name": ""
      }
      ]
      }
     */
    public function listOfShop(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $flag = $request->input('flag', 0);
        if (!in_array($flag, [2, 3])) {
            return $this->error(0, '参数错误');
        }
        $model = new FreightTemplate();
        $data = $model->listOfShop($flag);
        return $this->success($data);
    }

}
