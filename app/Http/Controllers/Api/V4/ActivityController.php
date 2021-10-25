<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;

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
        return [
            '2x'=>[
                "/nlsg/activity/%402x/1%402x.png",
                "/nlsg/activity/%402x/2%402x.png",
                "/nlsg/activity/%402x/3%402x.png",
                "/nlsg/activity/%402x/4%402x.png",
                "/nlsg/activity/%402x/5%402x.png",
                "/nlsg/activity/%402x/6%402x.png",
                "/nlsg/activity/%402x/7%402x.png",
                "/nlsg/activity/%402x/8%402x.png",
            ],
            '3x'=>[
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
    }


}
