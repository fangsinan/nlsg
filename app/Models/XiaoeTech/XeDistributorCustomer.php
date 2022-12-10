<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;

class XeDistributorCustomer extends Base
{
    const DB_TABLE = 'nlsg_xe_distributor_customer';
    protected $table = 'nlsg_xe_distributor_customer';

    protected $fillable = [
        'xe_user_id',
        'sub_user_id',
        'wx_nickname',
        'wx_avatar',
        'order_num',
        'sum_price',
        'bind_time',
        'status',
        'status_text',
        'remain_days',
        'expired_at',
        'is_editable',
        'is_anonymous',
    ];

}
