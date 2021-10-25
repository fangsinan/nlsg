<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\LiveCountDown;
use App\Models\MallOrder;
use App\Models\MeetingSales;
use App\Models\MeetingSalesBind;
use App\Models\Order;
use App\Models\VipUser;
use App\Models\VipUserBind;
use Illuminate\Http\Request;

/**
 * Description of AddressController
 *
 * @author wangxh
 */
class ActivityController extends Controller {


    /**
     * @api {get} api/v4/activity/activeImg  获取活动图片
     * @apiVersion 4.0.0
     * @apiName  activeImg
     * @apiGroup im
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function activeImg(){

        $data = [
            'twox'=>[
                "/nlsg/activity/%402x/1%402x.png",
                "/nlsg/activity/%402x/2%402x.png",
                "/nlsg/activity/%402x/3%402x.png",
                "/nlsg/activity/%402x/4%402x.png",
                "/nlsg/activity/%402x/5%402x.png",
                "/nlsg/activity/%402x/6%402x.png",
                "/nlsg/activity/%402x/7%402x.png",
                "/nlsg/activity/%402x/8%402x.png",
            ],
            'threex'=>[
                "/nlsg/activity/%402x/1%402x.png",
                "/nlsg/activity/%402x/2%402x.png",
                "/nlsg/activity/%402x/3%402x.png",
                "/nlsg/activity/%402x/4%402x.png",
                "/nlsg/activity/%402x/5%402x.png",
                "/nlsg/activity/%402x/6%402x.png",
                "/nlsg/activity/%402x/7%402x.png",
                "/nlsg/activity/%402x/8%402x.png",
            ],
        ];
        return success($data);
    }




    /**
     * @api {post} /api/v4/activity/create_activity 活动下单
     * @apiName create_activity
     * @apiVersion 1.0.0
     * @apiGroup activity
     *
     * @apiParam {int} product_id      产品id
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     * @apiParam {int} inviter 推客id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */

    public function createNewVipOrder(Request $request)
    {
        $level = 1;
        $os_type = $request->input('os_type', 0);
        $live_id = $request->input('live_id', 0);
        $live_num = $request->input('live_num',1);
        $tweeter_code = intval($request->input('inviter', 0));  //推客id
        $user_id = $this->user['id'];


        //检测下单参数有效性
        if (empty($user_id)) {
            return $this->error(0, '用户id有误');
        }

        /*********************** 校验推客身份   *********************/
        //先校验直播预约的tweeter_code
        if ($live_id) {
            $info = LiveCountDown::where(['user_id' => $user_id, 'live_id' => $live_id,])->get('new_vip_uid');
            if (!empty($info->new_vip_uid) && $info->new_vip_uid > 0) {
                $vip_check = VipUser::where(['status' => 1, 'is_default' => 1, 'user_id' => $info->new_vip_uid])->get()->toArray();
                if ($vip_check) {
                    $tweeter_code = $info['new_vip_uid'];
                }
            }
        }
        /***********************   销讲老师绑定经销商   *********************/
        $sales_id = $request->input('sales_id') ?? 0;
        $sales_bind_id = 0;
        if( isset($sales_id) && $sales_id > 0 ){
            $now_date = date('Y-m-d H:i:s', time());
            $sales_data = MeetingSales::where(['id'=>$sales_id,'status'=>1])->first();
            $sales_bind = MeetingSalesBind::where(['sales_id'=>$sales_id,'status'=>1])
                ->where('begin_at', '<=', $now_date)
                ->where('end_at', '>=', $now_date)->first();


            if($sales_data['type'] == 1 && !empty($sales_bind) ){
                $check_dealer = VipUser::where('id', '=', $sales_bind['dealer_vip_id'])
                    ->where('level', '=', 2)
                    ->where('is_default', '=', 1)
                    ->where('status', '=', 1)
                    ->where('expire_time', '>=', $now_date)
                    ->first();
                if($check_dealer){
                    $tweeter_code = $sales_bind['dealer_user_id'];  //经销商id
                }
                $sales_bind_id = $sales_bind['id'];
            }
        }
        /***********************   销讲老师绑定经销商   *********************/



        //新会员关系保护
        $remark = '';
        $bind_user_id = VipUserBind::getBindParent($this->user['phone']);
        if ($bind_user_id == -1) {
            $remark = $tweeter_code . '->' . 0;
            $tweeter_code = 0;
        } else {
            if ($bind_user_id != 0 && $tweeter_code !== $bind_user_id) {
                $remark = $tweeter_code . '->' . $bind_user_id;
                $tweeter_code = $bind_user_id;
            }
        }


        //判断推客身份是否过期
        if (!empty($tweeter_code)) {
            $is_vip = VipUser::IsNewVip($tweeter_code);
            if (!$is_vip) {
                $tweeter_code = 0;
            }
        }

        /*********************** 校验推客身份   *********************/


        if (!in_array($level, [1, 2])) {
            return $this->error(0, 'vip类型有误');
        }

        if ($level == 1) {
            $price = 360;
        } else {
            $price = 1000;
        }


        $type = 1;
        if ($this->user['new_vip']['level'] > 0) { //续费
            if ($level == 1) { //360 会员
                $type = 2;
            }
        }


        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 16,
            'user_id' => $user_id,
            'relation_id' => $level,
            'price' => $price,
            'cost_price' => $price,
            'live_num'=>$live_num,
            'ip' => $this->getIp($request),
            'os_type' => $os_type,
            'live_id' => $live_id,
            'vip_order_type' => $type,  //1开通 2续费 3升级
            'remark' => $remark,
            'twitter_id' => $tweeter_code,
            'sales_id' => $sales_id,
            'sales_bind_id' => $sales_bind_id,
        ];

        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }


}
