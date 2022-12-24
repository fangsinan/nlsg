<?php


namespace App\Models;


class CommandJobLog extends Base
{
    protected $table = 'nlsg_command_job_lob';

    public static function add($method, $params)
    {
        unset($params['command']);
        $params = http_build_query($params);

        self::query()->insert([
            'method' => $method,
            'params' => $params,
        ]);
    }

}
