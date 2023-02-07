<?php

namespace App\Models\Xfxs;

use App\Models\Base;

class XfxsOrder extends Base
{
    const DB_TABLE = 'xfxs_order';
    protected $table = 'xfxs_order';

    protected $fillable = [
        'created_at'
    ];
}
