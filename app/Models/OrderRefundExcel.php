<?php

namespace App\Models;

class OrderRefundExcel extends Base
{

    protected $table = 'nlsg_order_refund_excel';
    protected $fillable = ['file_name', 'url', 'admin_id', 'created_at', 'updated_at'];

}
