<?php


namespace App\Models;


class ChannelSku extends Base
{

    protected $table = 'nlsg_channel_sku';

    public static function checkSku($sku,$channel){
        $check = self::where('sku','=',$sku)
            ->where('channel','=',$channel)
            ->select(['id'])
            ->first();
        if ($check){
            return 1;
        }else{
            return 0;
        }
    }
}
