<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Qrcodeimg;
use Illuminate\Http\Request;

class ImageController extends Controller
{

   /**
     * @api {get} api/v5/image/get_qr_code 支付成功弹窗
     * @apiVersion 5.0.0
     * @apiName  get_qr_code
     * @apiGroup FiveCode
     *
     * @apiParam {number} relation_type 类型 1.精品课程2.商城3.直播   4 购买360   5 大咖讲书  6训练营
     * @apiParam {number} relation_id   数据id 课程id  商品id  直播id
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function GetQrCode(Request $request){

        $relation_type = $request->input('relation_type')??0;
        $relation_id = $request->input('relation_id')??0;
        $order_id = $request->input('order_id')??0;
        $is_wechat = $request->input('is_wechat')??0;
        if($relation_type == 3){
            if(empty($order_id) && empty($is_wechat)){ //免费并且是渠道不弹
                return success((object)[] );
            }
            //付费客户端不传直播id  需要查询
            if(!empty($order_id)){  //付费
                $order = Order::where(['id'=>$order_id])->first();
                $relation_id = $order['relation_id'];
            }

        }else{
            //目前除了直播 其他不需要根据各个具体产品返二维码
            $relation_id = 0;
        }

        $res = Qrcodeimg::select("id","qr_url")->where([
            'relation_type' => $relation_type,
            'relation_id'   => $relation_id,
            'status'   => 1,
        ])->get();

        if(empty($res)){
            $res=[];
        }

        return success($res);
    }
}