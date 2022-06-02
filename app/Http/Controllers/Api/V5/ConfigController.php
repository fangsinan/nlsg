<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\Lists;
use App\Models\Subscribe;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
     /**
     * {get} api/v5/config/share  全局分享
     * @apiVersion 5.0.0
     *
     * @apiParam {string} user_id  用户id
     * @apiParam {string} type     默认1   110专栏  120课程  130讲座  140训练营  150 商品  160集合（161 大咖讲书） 
     */
    public function share(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(1000,$validator->messages()->first(),(object)[]);
        }
        $type = $request->input('type', 1);
        $version = $request->input('version', 0);
        $os_type = $request->input('os_type', 0);
        
        $res_share = [
            "title"         => "",
            "share_image"   => "",
            "subtitle"      => "",
            "share_url"     => "",
        ];

        switch($type){
            case 161:
                $res_share = [
                    "title"         => "大咖讲书",
                    "share_image"   => "/nlsg/lists/dakajiangshu_pic.jpg",
                    "subtitle"      => "大咖讲书副标题",
                    "share_url"     => "https://wechat.nlsgapp.com/activeShare?id=95",
                ];
                break;
            default:
                break; 
        }
        return $this->success($res_share);
    }

}
