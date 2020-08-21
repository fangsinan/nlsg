<?php


namespace App\Servers;


use App\Models\MallRefundRecord;

class CrontabServers
{
    public function mallRefund()
    {

        $list = MallRefundRecord::from('nlsg_mall_refund_record as mrr')
            ->join('nlsg_mall_order as mo', 'mrr.order_id', '=', 'mo.id')
            ->join('nlsg_pay_record as pr', 'pr.ordernum', '=', 'mo.ordernum')
            ->where('mrr.run_refund', '=', 1)
            ->where('pr.order_type', '=', 10)
            ->select(['mrr.id as service_id', 'service_num', 'mrr.order_id',
                'mrr.order_detail_id', 'mrr.type', 'mrr.pay_type',
                'mrr.status as service_status', 'mrr.user_id', 'pr.transaction_id',
                'pr.ordernum', 'pr.price as all_price', 'mrr.price as refund_price'])
            ->get();

        foreach ($list as $v){

        }


        dd($list->toArray());
    }
}
