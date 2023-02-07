<?php


namespace App\Models\Xfxs;

use App\Models\Base;
class XfxsVip extends Base
{
    protected $table = 'xfxs_vip';
    const DB_TABLE          = 'xfxs_vip';
    const NEW_PRICE         = '2580';//新开通价格
    const NEW_TWITTER_PRICE = '500';//收益
    const NEW_PRICE_DAY     = '7.07';
    const OLD_PRICE         = '500';//续费价格
    const OLD_PRICE_DAY     = '1.36';

    protected $fillable = [];

    public function checkVipByUid($uid = 0): array
    {
        if (!$uid) {
            return [];
        }

        $check = self::query()
            ->where('user_id', '=', $uid)
            ->where('status', '=', 1)
            ->select([
                         'id', 'user_id', 'username', 'level',
                         'start_time', 'expire_time',
                     ])
            ->first();

        if ($check) {
            return $check->toArray();
        }

        return [];
    }


    //是否vip
    public static function UserIsVip($uid): bool
    {

        $vip = XfxsVip::where([
            "status"=>1,
            "user_id"=>$uid
        ])->first();


        $res = false;
        if(!empty($vip)){
            $res = true;
        }
        return $res;
    }
}
