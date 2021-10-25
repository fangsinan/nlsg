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
//            'twox'=>[
//                "/nlsg/activity/%402x/1%402x.png",
//                "/nlsg/activity/%402x/2%402x.png",
//                "/nlsg/activity/%402x/3%402x.png",
//                "/nlsg/activity/%402x/4%402x.png",
//                "/nlsg/activity/%402x/5%402x.png",
//                "/nlsg/activity/%402x/6%402x.png",
//                "/nlsg/activity/%402x/7%402x.png",
//                "/nlsg/activity/%402x/8%402x.png",
//            ],
//            'threex'=>[
//                "/nlsg/activity/%403x/1%403x.png",
//                "/nlsg/activity/%403x/2%403x.png",
//                "/nlsg/activity/%403x/3%403x.png",
//                "/nlsg/activity/%403x/4%403x.png",
//                "/nlsg/activity/%403x/5%403x.png",
//                "/nlsg/activity/%403x/6%403x.png",
//                "/nlsg/activity/%403x/7%403x.png",
//                "/nlsg/activity/%403x/8%403x.png",
//            ],
            'img' =>[
                "top" => "/nlsg/activity/13611635153705_.pic_hd.jpg",
                "down" => "/nlsg/activity/13621635153710_.pic_hd.jpg",
            ]
        ];
        return success($data);
    }

}
