<?php


namespace App\Models;


class PayRecordDetail extends Base
{
    protected $table = 'nlsg_pay_record_detail';

    protected $fillable = ['ordernum' , 'type' , 'user_id', 'price' , 'order_detail_id'  , 'source_id' , 'subsidy_type' ,];


}