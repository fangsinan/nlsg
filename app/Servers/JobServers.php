<?php


namespace App\Servers;


use App\Jobs\JobOfCytx;
use App\Jobs\JobOfSocket;

class JobServers
{
    //直播的
    public static function pushToSocket($live_id, $live_info_id, $type)
    {
        //$data = ['live_id' => $live_id, 'live_info_id' => $live_info_id, 'type' => $type];
        //JobOfSocket::dispatch($data);
    }

    //创业天下推送
    public static function pushToCytx($order_id){
        JobOfCytx::dispatch(['id'=>$order_id]);
    }
}
