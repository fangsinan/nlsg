<?php

namespace App\Models;

class OrderErpList extends Base
{
    protected $table = 'nlsg_order_erp_list';

    protected $fillable = [
        'order_id', 'flag', 'created_at', 'updated_at'
    ];

    public function orderInfo() {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public static function addList($order_id): bool {
        $check_order = Order::query()
            ->where('id', '=', $order_id)
            ->where('status', '=', 1)
            ->whereIn('type', [14, 18])
            ->select(['id', 'pay_price'])
            ->first();

        if (empty($check_order)) {
            return true;
        }

        $flag = ConfigModel::getData(56, 1);
        switch (intval($flag)) {
            case 1:
                if ($check_order->pay_price <= 0.01) {
                    return true;
                }
                break;
            case 2:
                if ($check_order->pay_price > 0.01) {
                    return true;
                }
                break;
        }

        $check_list = self::query()
            ->where('order_id', '=', $order_id)
            ->where('flag', '=', 1)
            ->first();
        if (!empty($check_list)) {
            return true;
        }

        $m           = new self();
        $m->order_id = $order_id;
        $m->save();
        return true;
    }
}
