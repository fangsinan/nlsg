<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianOrder extends Base
{

    protected $table = 'nlsg_dou_dian_order';

    protected $fillable = [
        'order_id', 'order_status', 'order_status_desc', 'main_status', 'main_status_desc',
        'pay_time', 'finish_time', 'create_time', 'update_time', 'order_amount', 'pay_amount',
        'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
        'post_addr_province_name', 'post_addr_city_name',
        'post_addr_town_name', 'post_addr_street_name', 'post_addr_province_id', 'post_addr_city_id',
        'post_addr_town_id', 'post_addr_street_id',
    ];

}
