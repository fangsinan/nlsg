<?php

namespace App\Models;

class LivePayCheck extends Base
{
    protected $table = 'nlsg_live_pay_check';
    static $table_name = 'nlsg_live_pay_check';
    protected $fillable = ['live_id', 'teacher_id', 'user_id', 'order_id', 'ordernum','is_zero'];

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

    public function livePayCheckToSub(int $user_id = 0, int $live_id = 0): array
    {
        if (!$user_id || !$live_id) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_live = Live::query()->where('id', '=', $live_id)->select(['id', 'user_id'])->first();

        if (!$check_live) {
            return ['code' => false, 'msg' => '直播错误'];
        }

        $check = self::checkByUid($user_id, $check_live->user_id);
        if (!$check) {
            return ['code' => false, 'msg' => '没有预约权限'];
        }

        $now = date('Y-m-d H:i:s');

        $res = Subscribe::query()
            ->firstOrCreate([
                'type'        => 3,
                'user_id'     => $user_id,
                'relation_id' => $live_id,
                'status'      => 1,
                'start_time'  => $now,
                'end_time'    => $now,
                'pay_time'    => $now,
                'remark'      => '基本库转入',
            ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];

    }

}
