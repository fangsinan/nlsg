<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class WechatOpenid extends Base
{

    protected $table = 'nlsg_wechat_openid';
    const DB_TABLE = 'nlsg_wechat_openid';

    /**
     * @return string
     * 添加
     */
    public static function Add($data,$flag){

        if($flag){
            $rst = DB::table(self::DB_TABLE)->insert($data);
        }else{
            $rst = self::query()->insertGetId($data);
        }

        return $rst;
    }

}
