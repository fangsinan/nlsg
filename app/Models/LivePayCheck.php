<?php

namespace App\Models;

class LivePayCheck extends Base
{
    protected $table = 'nlsg_live_pay_check';
    static $table_name = 'nlsg_live_pay_check';
    protected $fillable = ['live_id', 'teacher_id', 'user_id', 'order_id', 'ordernum'];

}
