<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class VipRedeemAssign extends Base
{
    protected $table = 'nlsg_vip_redeem_assign';

    //钻石兑换码配额统计
    public static function statistics($user)
    {
        $vip_id = $user['new_vip']['vip_id'] ?? 0;
        if (empty($vip_id)) {
            $all_count = 0;
            $created_count = 0;
        } else {
            $all_count = self::where('receive_vip_id', '=', $vip_id)
                ->where('receive_uid', '=', $user['id'])
                ->where('status', '=', 1)
                ->sum('num');

            $created_count = VipRedeemUser::where('user_id', '=', $user['id'])
                ->where('vip_id', '=', $vip_id)
                ->where('parent_id', '=', 0)
                ->count();
        }
        $res['all_count'] = $all_count;
        $res['created_count'] = $created_count;
        $res['can_use'] = $all_count - $created_count;
        return $res;
    }

}
