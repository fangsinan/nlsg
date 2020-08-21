<?php


namespace App\Servers;


use App\Models\MallRefundRecord;

class CrontabServers
{
    public function mallRefund(){
        $list = MallRefundRecord::where('run_refund','=',1)->get();

        //$query = MallOrder::from('nlsg_mall_order as nmo')
        //            ->join('nlsg_mall_group_buy_list as gbl', 'nmo.id', '=', 'gbl.order_id');
        $list = '';


        dd($list->toArray());
    }
}
