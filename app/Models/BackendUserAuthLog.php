<?php

namespace App\Models;

class BackendUserAuthLog extends Base
{
    protected $table = 'nlsg_backend_user_auth_log';

    protected $fillable = [
        'admin_id',
        'ip',
        'uri',
        'host',
        'input',
    ];

}
