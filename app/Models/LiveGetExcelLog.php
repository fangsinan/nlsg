<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveGetExcelLog extends Model
{
    protected $table = 'nlsg_live_get_excel_log';

    protected $fillable = [
        'mothed','admin_id','begin_time','end_time','live_id','is_watch','begin_time_d1','end_time_d1',
        'begin_time_d2','end_time_d2','is_bind'
    ];

}
