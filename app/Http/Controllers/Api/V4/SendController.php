<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\Works;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class SendController extends Controller
{

    /**
     * @api {get} api/v4/send/get_send_order 赠送订单详情
     * @apiName get_send_order
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} order_id 订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     *
     */
    public function getSendOrder(Request $request){

        $order_id = $request->input('order_id',0);
        $user_id   = $this->user['id'] ?? 0;

        $data = Order::select('id', 'send_type', 'relation_id', 'user_id', 'status', 'price', 'pay_price', 'coupon_id', 'pay_time', 'ordernum', 'created_at', 'pay_type','remark','send_user_id')
            ->where(['id' => $order_id,'type'=>17])->first()->toArray();
        if( empty($data) ) {
            return $this->error(0,'订单不存在');
        }
        $user_data = User::find($data['user_id']);
        $relation_data = [];
        if( $data['send_type'] == 2 ){
            //查询当前课程
            $relation_data = Works::select(['id','column_id','user_id' ,'type','title','subtitle', 'original_price', 'price', 'cover_img','detail_img','message','content','is_pay','is_end','is_free','subscribe_num','collection_num','comment_num','chapter_num','is_free'])
                ->where('status',4)->find( $data['relation_id'] );
            $relation_data['user_info'] = User::find($relation_data['user_id']);

        } elseif($data['send_type'] == 1 || $data['send_type'] == 6 ){
            $field = ['id', 'name', 'column_type', 'title', 'subtitle', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num','collection_num','comment_num','info_num','is_free','category_id','info_num'];
            $relation_data = Column::getColumnInfo($data['relation_id'],$field,$user_id);
            if( empty($relation_data) ) {
                return $this->error(0,'该信息不存在');
            }
        }


        return $this->success( ['user_data' =>$user_data, 'order_data'=>$data,'send_data' =>$relation_data] );
    }



    /**
     * @api {get} api/v4/send/send_edit 领取操作
     * @apiName send_edit
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} order_id 订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     *
     */
    public function getSendEdit(Request $request){

        $order_id = $request->input('order_id',0);
        $user_id   = $this->user['id'] ?? 0;

        $data = Order::select('id', 'send_type', 'relation_id', 'user_id', 'status', 'price', 'pay_price', 'coupon_id', 'pay_time', 'ordernum', 'created_at', 'pay_type')
            ->where(['id' => $order_id,'type'=>17])->first()->toArray();

        $re = true;
        DB::beginTransaction();
        try {
            $orderRst = Order::where(['ordernum' => $data['ordernum']])->update(['send_user_id'=>$user_id]);

            $starttime = strtotime(date('Y-m-d', time()));
            $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期

            $subscribe = [
                'user_id' => $user_id, //会员id
                'pay_time' => $data['pay_time'], //支付时间
                'type' => $data['send_type'],
                'status' => 1,
                'order_id' => $data['id'], //订单id
                'relation_id' => $data['relation_id'],
                'start_time' => date("Y-m-d H:i:s", $starttime),
                'end_time' => date("Y-m-d H:i:s", $endtime),
            ];
            $subscribeRst = Subscribe::firstOrCreate($subscribe);


            if ($orderRst  && $subscribeRst ) {
                DB::commit();
            } else {
                $re = false;
                DB::rollBack();
            }

        } catch (\Exception $e) {
            $re = false;
            DB::rollBack();
        }
        if($re){
            return $this->success();
        }else{
            return $this->error(0,'领取失败');
        }


    }


}
