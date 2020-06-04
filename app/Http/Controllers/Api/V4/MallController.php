<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MallGoods;

class MallController extends Controller {

    //商品列表 猜你喜欢
    public function goods_list(Request $request) {

        $params = $request->input();
        $params['page'] = 1;
        $params['size'] = 4;
        $user = ['id' => 4, 'level' => 4, 'is_staff' => 1];
        $model = new MallGoods();
        $data = $model->getList($params,1,$user);
        
        $res = [
            'code'=>200,
            'msg'=>'ok',
            'data'=>$data
        ];
        return response()->json($res);
    }

    //商品可用优惠券列表
    public function couponList(Request $request) {
//        $model = new \App\Model\CouponRuleModel();
//        $res = $model->getList($request);
//        return $this->writeJson(Status::CODE_OK, 'ok', $res);
    }

    //商品评论列表
    public function commentList(Request $request) {
//        $model = new \App\Model\MallCommentModel();
//        $res = $model->getList($request);
//        if ($res['code'] === false) {
//            return $this->writeJson(Status::CODE_FAIL, 'ok', $res);
//        } else {
//            return $this->writeJson(Status::CODE_OK, 'ok', $res);
//        }
    }

}
