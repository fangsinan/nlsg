<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ActionStatistics;
use App\Models\ConfigModel;
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
     * @api {get} api/v4/activity/activeImg  获取活动信息
     * @apiVersion 4.0.0
     * @apiName  activeImg
     * @apiGroup activity
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
        code: 200,
        msg: "成功",
        data: {
            img: {
                top: "/nlsg/activity/13611635153705_.pic_hd.jpg",
                down: "/nlsg/activity/13631635154129_.pic_hd.jpg"
            },
            is_pay_order: 0,        //是否购买
            is_pay_order_count: 0   //购买数
            active_flag: 0   //活动标识  2021-11-1 || 2021-11-2
            active_status: 1   //活动状态
        }
    }
     */
    public function activeImg(Request $request){

        $user_id = $this->user['id'] ?? 0;

        ActionStatistics::actionAdd(1,$user_id,$request->input("os_type")??0);
        $active_status = 1;
        $now = time();

        $active_time = ConfigModel::getData(61,1);
        $active_time = explode(',',$active_time);
        $begin_time = strtotime($active_time[0]);
        $end_time = strtotime($active_time[1]);
        $is_end = 0;


        if ( $now <= $begin_time || $now > $end_time ){
            $active_status = 0;
//            return $this->error(0, "活动未开始");
        }
        if ($now > $end_time){
            $is_end = 1;
        }

        $is_h5 = $request->input('is_h5','0');
        if (empty($is_h5)){
            $tag = ConfigModel::getData(60,1);
        }else{
            $tag = $request->input('activity_tag','2021-11-1');
        }

        //初始化数据
        $data = [
            'img' =>[
                "top" => "/nlsg/works/20211027172729660684.png",
                "down" => "/nlsg/works/20211101114459938275.png",
            ],
            'is_pay_order' =>(string)0,         //是否购买
            'is_pay_order_count' =>(string)0,   //购买数量
            'active_flag' =>$tag,       //活动标识 -1 or -2
            'active_status' =>(string)$active_status,   //1|0 开始  未开始
        ];

        if($tag === "2021-11-1"){ //1号活动
            $data['img'] = [
                "top" => "/nlsg/works/20211027172729660684.png",
                "down" => "/nlsg/works/20211101114459938275.png",
            ];
        }else if($tag === "2021-11-2"){ //2号活动
            $data['img'] = [
                "top" => "/nlsg/works/20211027174906146584.png",
                "down" => "/nlsg/works/20211028140639817857.png",
            ];
        }else{
            $this->error(0, "活动未开始");
        }

        $order = Order::select("id")->where([
            'user_id' => $user_id,
            'activity_tag' => $tag,
            'relation_id' => 1,
            'type' => 16,
            'status' => 1,
        ])->first();
        if(!empty($order)){
            $data['is_pay_order'] = 1;
        }

        if ($active_status===1){
            $order_count= Order::select("id")->where([
                'activity_tag' => $tag,
                'relation_id' => 1,
                'type' => 16,
                'status' => 1,
            ])->count();
            //初始值3.5w  每单加100
            $is_pay_order_count = (35000 + $order_count*100);
            $data['is_pay_order_count'] = $is_pay_order_count >= 10000 ? $is_pay_order_count/10000 .'万' : $is_pay_order_count;
        }else{
            $data['is_pay_order_count'] = 0;
        }

        $data['is_end'] = $is_end;
        return success($data);
    }




    /**
     * @api {get} api/v4/activity/track  双十一活动埋点
     * @apiVersion 4.0.0
     * @apiName  track
     * @apiGroup activity
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
        code: 200,
        msg: "成功",
        data: {
        }
    }
     */
    public function trackStatistics(Request $request){

        ActionStatistics::actionAdd($request->input("type"),$this->user['id'] ?? 0,$request->input("os_type")??0);
        return success();

    }

}
