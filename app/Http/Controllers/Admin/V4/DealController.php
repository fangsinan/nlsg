<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Servers\DealServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DealController extends Controller
{

    /**
     * 验证表单
     * @param $data
     * @param $rules
     * @param $messages
     * @return bool|string
     */
    protected function validator($data, $rules, $messages)
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        return true;
    }

    /**
     * @api {get} api/admin_v4/deal/get_order_info  获取成交订单
     * @apiVersion 4.0.0
     * @apiName  getOrderInfo
     * @apiGroup Deal
     */
    //https://app.v4.api.nlsgapp.com/api/admin_v4/deal/get_order_info?live_id=122
    public function getOrderInfo(Request $request)
    {

        $data = $request->input();
        $live_id = intval($request->get('live_id', 0));
        /*$rules = [
//            'live_id'     =>  'required',
            'start_time'     =>  'required',
            'end_time'     =>  'required',
        ];
        $messages = [
//            'live_id.required'     =>  '直播id不能为空',
            'start_time.required'     =>  '开始时间不能为空',
            'end_time.required'     =>  '结束时间不能为空',
        ];

        if (true !== ($error = $this->validator($data, $rules, $messages))) {
            return $this->error(0,$error);
        }*/

        $DealObj=new DealServers();
        $RstData=$DealObj::getOrderInfo($data,$live_id,1);
        if($RstData['status']!=1){
            return $this->error(0, $RstData['msg']);
        }else{
            return $this->success($RstData['data'],0,$RstData['msg']);
        }
    }

}
