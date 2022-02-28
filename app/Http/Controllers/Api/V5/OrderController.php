<?php


namespace App\Http\Controllers\Api\V5;


use App\Http\Controllers\Controller;
use App\Models\MallAddress;
use App\Models\Order;
use Illuminate\Http\Request;


/**
 * 下单Controller
 * 虚拟作品订单操作
 */
class OrderController extends Controller
{


    /**
     * @api {post} /api//v5/order/create_order_address 虚拟订单地址上报
     * @apiName create_order_address
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} address_id      地址id
     * @apiParam {int} order_id      订单
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function addOrderAddress(Request $request)
    {
        $user_id = $this->user['id']??0;

        $address_id = $request->input('address_id', 0);
        $order_id = $request->input('order_id', 0);
        if(!empty($address_id) && !empty($order_id)){

            $address = MallAddress::where([
                'id'=>$address_id,
                'is_del' => 0,
                'user_id'=>$user_id
            ])->first('id');

            if(empty($address)){
                $this->error();
            }

            $res = Order::where(['id' => $order_id,'user_id'=>$user_id ])->update([
                'address_id'=>$address_id,
            ]);
            if(!empty($res)){
                return $this->success();
            }
        }
        return $this->error();

    }

}
