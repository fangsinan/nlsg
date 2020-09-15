<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Description of Config
 *
 * @author wangxh
 */
class ConfigModel extends Base
{

    protected $table = 'nlsg_config';

    //1:邮费  2:特价优先级
    public static function getData($id)
    {
        $expire_num = 3600;
        $cache_key_name = 'v4_config_' . $id;

        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getFromDb($id);
            Cache::put($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

    protected static function getFromDb($id)
    {
        $res = ConfigModel::find($id)->toArray();
        return $res['value'];
    }

}
