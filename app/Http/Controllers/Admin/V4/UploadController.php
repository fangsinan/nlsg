<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use Illuminate\Http\Request;


class UploadController extends ControllerBackend
{


    public function file(Request $request)
    {
        $type = $request->get('type') ?? 'video';
        if ($type == 'video') {
            $data = $this->upload(320586);
        } elseif ($type == 'audio') {
            $data = $this->upload(459377, 0);
        } elseif ($type == 'short_video') {
            $data = $this->upload(867416, 0);
        }
        return success($data);
    }

    public function upload($classId = 320586, $isTranscode = 1)
    {
        $toQColudIsTest = config('env.toQColudIsTest');
        if ($toQColudIsTest === 1) {
            $classId = 617211;
        }
        //320586 视频 453220 排课系统 459377 音频  617211 测试上传
        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + 86400;  // 签名有效期：1天

        // 向参数列表填入参数
        $arg_list = [
            "secretId"         => config('env.QSECRET_ID'),
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
            hash_hmac('SHA1', $orignal, config('env.QSECRET_KEY'), true).$orignal
        );

        return  $signature;
    }

}
