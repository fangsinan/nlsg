<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Live extends Model
{
    protected $table = 'nlsg_live';

    public function getIndexLive($ids)
    {
        if ( ! $ids) {
            return false;
        }
        $lists = $this->select('id', 'title', 'describe', 'cover_img', 'start_time', 'end_time')
            ->whereIn('id', $ids)
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as &$v) {
                if (strtotime($v['start_time']) > time()) {
                    $v['live_status'] = '未开始';
                } else {
                    if (strtotime($v['end_time']) < time()) {
                        $v['live_status'] = '已结束';
                    } else {
                        $v['live_status'] = '正在直播';
                    }
                }
            }
        }
        return $lists;
    }

    public function getLiveLists()
    {
        $lists = $this->with('user')
            ->where('status', 4)
            ->orderBy('begin_at','desc')
            ->get()
            ->toArray();
        return $lists;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
