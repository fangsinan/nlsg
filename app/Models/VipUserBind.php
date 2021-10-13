<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class VipUserBind extends Base
{
    protected $table = 'nlsg_vip_user_bind';

    //0没绑定   -1绑定但是无效   其他父类用户id
    public static function getBindParent($phone = '')
    {
        if (empty($phone)) {
            return 0;
        }
        $res = DB::table('nlsg_vip_user_bind as vub')
            ->leftJoin('nlsg_user as u', 'vub.parent', '=', 'u.phone')
            ->leftJoin('nlsg_vip_user as vu', function ($join) {
                $join->on('vu.user_id', '=', 'u.id')
                    ->where('vu.is_default', '=', 1)
                    ->where('vu.status', '=', 1);
            })
            ->where('son', '=', $phone)
            ->whereRaw('(life = 1 or (life = 2 AND FROM_UNIXTIME(UNIX_TIMESTAMP()) BETWEEN begin_at and end_at))')
            ->select(['parent', 'u.id as parent_user_id', 'vu.user_id as vuid'])
            ->first();

        if (empty($res)) {
            return 0;
        }
        if (empty($res->vuid ?? '')) {
            return -1;
        }
        return (int)$res->vuid;
    }

    public static function clear()
    {
        $clear_sql = "update  nlsg_vip_user_bind set status = 2 where status in (0,1) and end_at <= SYSDATE()";
        $res = DB::select($clear_sql);
        dd($res);
    }

}
