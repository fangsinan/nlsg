<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianOrderDecryptQuota extends Base
{

    protected $table = 'nlsg_dou_dian_order_decrypt_quota';

    protected $fillable = ['flag','expire','check','err_type','dou_dian_type'];

}
