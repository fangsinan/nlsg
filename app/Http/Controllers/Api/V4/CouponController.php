<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller {

    /**
     * 领取优惠券
     * @api {post} /api/v4/goods/get_coupon 领取优惠券
     * @apiVersion 4.0.0
     * @apiName /api/v4/goods/get_coupon
     * @apiGroup  coupon
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/goods/get_coupon
     * @apiDescription 领取优惠券
     * @apiParam {string} flag 优惠券规则id(31,32,33)
     *
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "msg": "领取成功"
      }
      }
     */
    public function getCoupon(Request $request) {
        $params = $request->input();

        if (empty($params['flag'] ?? 0)) {
            return $this->error(0, '参数错误');
        }

        $model = new Coupon();
        $data = $model->getCoupon($params['flag'], $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 我的优惠券列表
     * @api {get} /api/v4/coupon/list 我的优惠券列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/coupon/list
     * @apiGroup  coupon
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/coupon/list
     * @apiDescription 我的优惠券列表
     * @apiParam {number=1,2,3} status 标识(1未用,2已用,3过期)
     * @apiParam {number} [page] page
     * @apiParam {number} [size] size
     *
     * @apiSuccess {string[]} count 数量统计
     * @apiSuccess {number} count.status_1 未用数量
     * @apiSuccess {number} count.status_2 已用数量
     * @apiSuccess {number} count.status_3 过期数量
     * @apiSuccess {string[]} list 列表
     * @apiSuccess {string} list.id id
     * @apiSuccess {string} list.number 编号
     * @apiSuccess {string} list.name 名称
     * @apiSuccess {string} list.type 类型(1专栏 3商品 4免邮券 5课程)
     * @apiSuccess {string} list.price 面值
     * @apiSuccess {string} list.full_cut 满减线
     * @apiSuccess {string} list.explain 说明
     * @apiSuccess {string} list.begin_time 生效时间
     * @apiSuccess {string} list.end_time 失效时间
     * @apiSuccessExample {json} Request-Example:
      {
      "code": 200,
      "msg": "成功",
      "data": {
      "count": {
      "status_1": 0,
      "status_2": 0,
      "status_3": 4
      },
      "list": [
      {
      "id": 7,
      "number": "202006121535411310000787769",
      "name": "5元优惠券(六一专享)",
      "type": 3,
      "price": "5.00",
      "full_cut": "0.00",
      "explain": "六一活动期间",
      "begin_time": "2020-06-12 00:00:00",
      "end_time": "2020-06-28 23:59:59"
      },
      {
      "id": 8,
      "number": "202006121535411320000596680",
      "name": "10元优惠券(六一专享)",
      "type": 3,
      "price": "10.00",
      "full_cut": "99.00",
      "explain": "六一活动期间使用",
      "begin_time": "2020-06-12 00:00:00",
      "end_time": "2020-06-28 23:59:59"
      },
      {
      "id": 9,
      "number": "202006121535411330000634480",
      "name": "20元优惠券(六一专享)",
      "type": 3,
      "price": "20.00",
      "full_cut": "199.00",
      "explain": "六一活动期间使用",
      "begin_time": "2020-06-18 00:00:21",
      "end_time": "2020-06-28 23:59:59"
      },
      {
      "id": 10,
      "number": "202006121544321350000639494",
      "name": "测试免邮券",
      "type": 4,
      "price": "0.00",
      "full_cut": "0.00",
      "explain": "商品免邮券",
      "begin_time": "2020-06-12 00:00:00",
      "end_time": "2020-06-28 23:59:59"
      }
      ]
      }
      }
     */
    public function list(Request $request) {
        if (empty($this->user['id'] ?? 0)) {
            return $this->success([]);
        }
        $params = $request->input();
        $model = new Coupon();
        $data = $model->listInHome($this->user['id'], $params);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
