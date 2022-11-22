<?php

namespace App\Models;

class LivePayCheck extends Base
{
    protected $table = 'nlsg_live_pay_check';
    static $table_name = 'nlsg_live_pay_check';
    protected $fillable = ['live_id', 'teacher_id', 'user_id', 'order_id', 'ordernum'];

    /**
     * @param int $user_id 用户id
     * @param int $teacher_id 直播的老师id
     * @return int
     */
    public static function checkByUid(int $user_id = 0, int $teacher_id = 0): int
    {
        if (!$user_id || !$teacher_id) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');

        $check = self::query()
            ->where('teacher_id', '=', $teacher_id)
            ->where('user_id', '=', $user_id)
            ->where('begin_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('protect_end_time', '>=', $now)
                    ->orWhereNull('protect_end_time');
            })
            ->select('id')
            ->first();

        if ($check) {
            return 1;
        }

        return 0;
    }

    public function livePayCheckToSub(int $user_id = 0,int $live_id = 0){
        if (!$user_id || !$live_id) {
            return ['code'=>false,'msg'=>'参数错误'];
        }

        $check_live = Live::query()->where('id','=',$live_id)->select(['id','user_id'])->first();

        if (!$check_live){
            return ['code'=>false,'msg'=>'直播错误'];
        }


    }

}
