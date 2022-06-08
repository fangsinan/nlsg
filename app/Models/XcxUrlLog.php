<?php

namespace App\Models;

class XcxUrlLog extends Base
{
    protected $table = 'nlsg_xcx_url_log';

    protected $fillable = [
        'type','date','counts','create_time','update_time'
    ];
}
