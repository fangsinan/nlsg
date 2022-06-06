<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ConfigModel;
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
            return $this->error(1000,$validator->getMessageBag()->first(),(object)[]);
        }
        $uid = $this->user['id'] ?? 0;
        $type = $request->input('type', 1);
        $version = $request->input('version', 0);
        $os_type = $request->input('os_type', 0);
        
        $res_share = [
            "title"         => "",
            "share_image"   => "",
            "subtitle"      => "",
            "share_url"     => "",
        ];
        $host_url = ConfigModel::getData(45)?? 'https://wechat.nlsgapp.com/';

        switch($type){
            case 161:
                $id = Lists::where(['type'=>10])->value("id");
                $res_share = [
                    "title"         => "大咖讲书",
                    "share_image"   => "/nlsg/lists/dakajiangshu_pic.jpg",
                    "subtitle"      => "让听书成为一种生活方式",
                    // "share_url"     => "https://wechat.nlsgapp.com/activeShare?id=".$id."&user_id=".$uid,
                    "share_url"     => $host_url."activeShare?id=".$id."&user_id=".$uid,
                    
                ];
                break;
            default:
                break; 
        }
        return $this->success($res_share);
    }

}
