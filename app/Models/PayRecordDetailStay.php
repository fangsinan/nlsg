<?php


namespace App\Models;


class PayRecordDetailStay extends Base
{
    protected $table = 'nlsg_pay_record_detail_stay';

    protected $fillable = [
        'ordernum', 'user_id', 'order_detail_id', 'type',
    ];


}