<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;


class UploadController extends ControllerBackend
{


    public function file(Request $request)
    {
        $type = $request->get('type') ?? 'video';
        if ($type == 'video') {
            $data = $this->upload(855795);
        } elseif ($type == 'audio') {
            $data = $this->upload(855794, 0);
        } elseif ($type == 'short_video') {
            $data = $this->upload(867416, 0);
        }
        return success($data);
    }

    public function upload($classId = 320586, $isTranscode = 1)
    {

        $toQColudIsTest = config('env.toQColudIsTest');
        if ($toQColudIsTest === 1) {
            $classId = 855798;
        }
        //320586 视频 453220 排课系统 459377 音频  617211 测试上传
        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + 86400;  // 签名有效期：1天

        // 向参数列表填入参数
        $arg_list = [
            "secretId"         => config('env.TENCENT_SECRETID'),
            "currentTimeStamp" => $current,
            "expireTime"       => $expired,
            "random"           => rand()
        ];


        if ($isTranscode == 1) { //mp3不需要转码
            $arg_list['isTranscode'] = 1;
        }
        $arg_list['classId'] = $classId;//分类, 默认视频

        if ($classId == 320586 || $classId == 867416) {
            //音频不转码
            //            $arg_list['procedure'] = 'QCVB_SimpleProcessFile({30},0,10,10)';
            $arg_list['procedure'] = 'rwl_ptzm';
            $arg_list['isWatermark'] = 1;
        }

        if ($classId == 459377) {
            $arg_list['procedure'] = 'rwl_audio';
        }
        // 计算签名
        $orignal = http_build_query($arg_list);
        $signature = base64_encode(
            hash_hmac('SHA1', $orignal, config('env.TENCENT_SECRETKEY'), true).$orignal
        );

        return  $signature;
    }




    public function editVideoClassId(Request $request)
    {
        // 移除视频至删除分类
        $video_ids = $request->get('video_id');
        if(empty($video_ids) ){
            return success();
        }

        if(!is_array($video_ids)){
            $video_ids = [$video_ids];
        }

        //多个处理
        $uri = 'vod.tencentcloudapi.com';
        $secretKey = config('env.TENCENT_SECRETKEY');
        foreach($video_ids as $video_id){
            //加密
            $rand   = rand (100, 10000000); //9031868223070871051
            $time   = time ();
            $data_key = [
                'Action' => 'ModifyMediaInfo',
                'Version' => '2018-07-17',
                'Language' => "zh-CN",
                'FileId' => $video_id,
                'ClassId' => '855797', // 待删除
                'Region' => "",
                'SecretId' => config('env.TENCENT_SECRETID'),
                'Timestamp' => $time,
                'Nonce' => $rand,
                // 'SignatureMethod' => 'HmacSHA256',
            ];


            ksort ($data_key); //排序
            // 计算签名
            $srcStr    = "GET".$uri."/?" . http_build_query ($data_key);
            $signature = base64_encode (hash_hmac ('sha256', $srcStr, $secretKey, true)); //SHA1  sha256
            $signature = base64_encode(hash_hmac("sha1", $srcStr, $secretKey, true));
            $data_key['Signature'] = $signature;
            ksort ($data_key); //排序
            //拉取转码成功信息
            $url = "https://vod.tencentcloudapi.com/?".http_build_query ($data_key); //?Action=PullEvent&COMMON_PARAMS
            $info = ImClient::curlGet ($url);  //post
            // $info = json_decode($info,true);
            DB::table('nlsg_log_info')->insert([
                'url'           =>  'editVideoClassId:'.$request->fullUrl(),
                'parameter'     =>  $info,
                'user_id'       =>  $this->user['id'] ?? 0,
                'created_at'    =>  date('Y-m-d H:i:s', time())
            ]);
        }
        return success();
    }

}
