<?php

namespace App\Models;



class Qrcodeimg extends Base
{

    protected $table = 'nlsg_qrcode_img';

    protected $fillable = [
        'relation_type', 'relation_id', 'qr_url', 'status','qr_type',
    ];


}
