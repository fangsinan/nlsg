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
        $lists = $this->select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at')
            ->whereIn('id', $ids)
            ->where('is_del', 0)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                            ->where('status', 1)
                            ->orderBy('id','desc')
                            ->first();
                if (strtotime($channel['begin_at']) > time()) {
                   $v['live_status'] = '1';
                } else {
                   if (strtotime($channel['end_at']) < time()) {
                       $v['live_status'] = '2';
                   } else {
                       $v['live_status'] = '3';
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
