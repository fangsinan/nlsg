<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class VipUser extends Base
{
    protected $table = 'nlsg_vip_user';

    public static function newVipInfo($user_id)
    {
        $now_date = date('Y-m-d H:i:s');
        $check = self::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('start_time', '<', $now_date)
            ->where('expire_time', '>', $now_date)
            ->select(['id as vip_id','level', 'start_time', 'expire_time'])
            ->first();
        if (empty($check)) {
            return ['level' => 0, 'start_time' => '', 'expire_time' => ''];
        } else {
            return $check->toArray();
        }
    }
}
