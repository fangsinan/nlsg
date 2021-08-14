<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class OrderTwitterLog extends Base
{


    const DB_TABLE = 'nlsg_order_twitter_log';
    protected $table = 'nlsg_order_twitter_log';

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
