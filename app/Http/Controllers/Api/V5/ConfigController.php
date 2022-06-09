<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use App\Models\Lists;
use App\Models\Order;
use App\Models\Qrcodeimg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
     /**
     * {get} api/v5/config/share  全局分享
     * @apiVersion 5.0.0
     *
     * @apiParam {string} user_id  用户id
     * @apiParam {string} type     默认1   110专栏  120课程  130讲座  140训练营  150 商品  160集合（161 大咖讲书） 170直播
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

    /**
     * {get} api/v5/config/get_ali_proof  获取 Ali STS临时授权
     * @apiVersion 5.0.0
     */
    public function getAliProof()
    {
        $cache_key_name = 'ali_proof_key';
        $res = Cache::get($cache_key_name);
        if(empty($res)){
            $res = ConfigModel::AliProof();
            if(empty($res)){
                return $this->success((object)[]); 
            }
            $res['timestamp'] = strtotime($res['Expiration']);
            Cache::put($cache_key_name, $res, 3000);
        }
        return $this->success($res);
    }



    /**
     * {get} api/v5/config/pay_finish_view  获取 支付后是否弹窗显示
     * @apiVersion 5.0.0
     * @apiParam {number} relation_type 类型 110专栏  120课程  130讲座  140训练营  150 商品  160集合（161 大咖讲书） 170直播
     * @apiParam {number} relation_id   数据id 课程id  商品id  直播id
     */
    public function payFinishView(Request $request){

        $input_type = $request->input('relation_type')??0;
        $relation_id = $request->input('relation_id')??0;
        $order_id = $request->input('order_id')??0;
        $is_wechat = $request->input('is_wechat')??0;
        if($input_type == 170){

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
        $FuncTypes = FuncType($input_type);
        $res = Qrcodeimg::select("id","qr_url")->where([
            'relation_type' => $FuncTypes['qrcode_type'],
            'relation_id'   => $relation_id,
            'status'   => 1,
        ])->first();

        if(empty($res)){
            $res=(object)[];
        }

        return success($res);
    }

}
