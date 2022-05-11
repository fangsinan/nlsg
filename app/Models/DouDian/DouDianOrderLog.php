<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianOrderLog extends Base
{

    protected $table = 'nlsg_dou_dian_order_log';

    protected $fillable = ['code', 'err_no', 'message', 'sub_code', 'sub_msg', 'page', 'size', 'total','job_type'];

}
