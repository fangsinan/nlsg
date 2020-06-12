<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller {

    /**
     * 领取优惠券
     * @api {post} /api/V4/goods/get_coupon 领取优惠券
     * @apiVersion 4.0.0
     * @apiName /api/V4/goods/get_coupon
     * @apiGroup  Mall
     * @apiSampleRequest /api/V4/goods/get_coupon
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
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];

        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }

        if (empty($params['flag'] ?? 0)) {
            return $this->error('参数错误');
        }

        $model = new Coupon();
        $data = $model->getCoupon($params['flag'], $user['id']);
        if ($data['code'] ?? true === false) {
            return $this->error($data['msg']);
        } else {
            return $this->success($data);
        }
    }

}
