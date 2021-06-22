<?php


namespace App\Models;


class MallErpList extends Base
{
    protected $table = 'nlsg_mall_order_erp_list';

    public static function addList($order_id){
        $check_order = MallOrder::where('id','=',$order_id)->select([
            'id','pay_price'
        ])->first();

        if (empty($check_order)){
            return true;
        }

        $flag = ConfigModel::getData(56,1);
        switch (intval($flag)){
            case 1:
                if ($check_order->pay_price <= 0.01){
                    return true;
                }
                break;
            case 2:
                if ($check_order->pay_price > 0.01){
                    return true;
                }
                break;
        }

        $check_list = self::where('order_id','=',$order_id)
            ->where('flag','=',1)
            ->first();
        if (!empty($check_list)){
            return true;
        }

        $m = new self();
        $m->order_id = $order_id;
        $m->save();
    }
}
