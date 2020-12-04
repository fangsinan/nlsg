<?php


namespace App\Models;


class PayRecord extends Base
{
    protected $table = 'nlsg_pay_record';

    protected $fillable = ['ordernum', 'type', 'user_id', 'transaction_id', 'status', 'price', 'product_id', 'order_type', 'client', 'tax', 'live_id',];

    public function isUserCanSpendMoney($user_id, $flag, $money = 0)
    {

    }
}
