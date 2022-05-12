<?php

namespace App\Models\DouDian;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DouDianOrder extends Base
{

    protected $keyType = 'string';
    protected $primaryKey = 'order_id';
    protected $table = 'nlsg_dou_dian_order';

    protected $fillable = [
        'order_id', 'order_status', 'order_status_desc', 'main_status', 'main_status_desc',
        'pay_time', 'finish_time', 'create_time', 'update_time',
        'order_amount', 'pay_amount','post_amount',
        'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
        'post_addr_province_name', 'post_addr_city_name',
        'post_addr_town_name', 'post_addr_street_name', 'post_addr_province_id', 'post_addr_city_id',
        'post_addr_town_id', 'post_addr_street_id',
        'cancel_reason','buyer_words',
        'decrypt_step','decrypt_err_no','decrypt_err_msg',
    ];

    public function orderList(): HasMany {
        return $this->hasMany(DouDianOrderList::class,'parent_order_id','order_id');
    }

}
