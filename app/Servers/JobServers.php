<?php


namespace App\Servers;


use App\Jobs\JobOfSocket;

class JobServers
{
    //直播的
    public static function pushToSocket($live_id, $live_info_id, $type)
    {
        //$data = ['live_id' => $live_id, 'live_info_id' => $live_info_id, 'type' => $type];
        //JobOfSocket::dispatch($data);
    }
}
