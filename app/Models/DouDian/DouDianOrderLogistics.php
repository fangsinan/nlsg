<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianOrderLogistics extends Base
{

    protected $table = 'nlsg_dou_dian_order_logistics';

    protected $fillable = ['order_id', 'tracking_no', 'company', 'ship_time', 'delivery_id', 'company_name',];

}
