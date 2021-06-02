<?php


namespace App\Models;


class MallErpList extends Base
{
    protected $table = 'nlsg_mall_order_erp_list';

    public static function addList($order_id){
        $m = new self();
        $m->order_id = $order_id;
        $m->save();
    }
}
