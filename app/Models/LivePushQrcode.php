<?php

namespace App\Models;

class LivePushQrcode extends Base
{

    protected $table = 'nlsg_live_push_qrcode';
    protected $fillable = [
        'id', 'qr_url', 'status'
    ];

}
