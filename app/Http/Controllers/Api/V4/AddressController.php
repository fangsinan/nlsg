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
     * @apiGroup  Mall
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

    //todo 创建修改收获地址 post
    public function create(Request $request) {

        
        $validatedData = $request->validate([
            'province' => ['required'],
            'city' => ['required'],
            'area' => ['required'],
            'name' => ['required'],
        ]);






        return $this->success($validatedData);
//        $params = $request->input();
//        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
//        if (empty($user['id'] ?? 0)) {
//            return $this->error('未登录');
//        }
//        $model = new MallAddress();
//        $data = $model->create($params, $user['id']);
//        if ($data['code'] ?? true === false) {
//            return $this->error($data['msg']);
//        } else {
//            return $this->success($data);
//        }
    }

    //todo 收获地址列表 get
    //todo 修改收获地址状态 put
}
