<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;

class XeUserJob extends Base
{
    const DB_TABLE = 'nlsg_xe_user_job';
    protected $table = 'nlsg_xe_user_job';

    protected $fillable = [
        'parent_phone',
        'parent_xe_user_id',
        'son_phone',
        'son_xe_user_id',
        'parent_job',
        'son_job',
        'bind_job',
        'parent_job_time',
        'son_job_time',
        'bind_job_time',
        'parent_job_err',
        'son_job_err',
        'bind_job_err',
    ];

}
