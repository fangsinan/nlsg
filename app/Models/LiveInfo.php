<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/17
 * Time: 2:01 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class LiveInfo extends Model{
    protected $table = 'nlsg_live_info';


    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    //echo getPushUrl("123456","时间戳");
    function getPushUrl( $streamName, $time = null){
        $live_config = Config::getInstance()->getConf('web.Live_config');
        $key        = $live_config['Live_API_KEY'];
        $push_url   = $live_config['push_url'];
        $play_url   = $live_config['play_url'];
        if($key && $time){
//            $txTime = strtoupper(base_convert(strtotime($time),10,16));
            $txTime = strtoupper(base_convert(($time),10,16));
            //txSecret = MD5( KEY + streamName + txTime )
            $txSecret = md5($key.$streamName.$txTime);
            $ext_str = "?".http_build_query(array(
                    "txSecret"=> $txSecret,
                    "txTime"=> $txTime
                ));
        }

        $run =[
            'push_url'=>"rtmp://".$push_url."/live/".$streamName . (isset($ext_str) ? $ext_str : ""),
            'play_url' =>"http://".$play_url."/live/".$streamName .'.m3u8'. (isset($ext_str) ? $ext_str : ""),
            'play_url_flv' =>"http://".$play_url."/live/".$streamName .'.flv'. (isset($ext_str) ? $ext_str : ""),
        ];
        return $run;
    }


}