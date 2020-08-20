<?php


namespace App\Servers;


use App\Models\MallRefundRecord;

class CrontabServers
{
    public function mallRefund(){
        $list = MallRefundRecord::where('run_refund','=',1)->get();

        dd($list->toArray());
    }
}
