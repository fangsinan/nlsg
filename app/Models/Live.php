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
        $list = $this->select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at', 'price', 'order_num')
            ->whereIn('id', $ids)
            ->where('is_del', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($list) {
            $channel = LiveInfo::where('live_pid', $list->id)
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->first();
            $list['info_id'] = $channel->id;
        }
        $sclass = new \StdClass();
        return $list ?: $sclass;
    }

    public function getLiveLists()
    {
        $lists = $this->with('user')
            ->where('status', 4)
            ->orderBy('begin_at', 'desc')
            ->get()
            ->toArray();
        return $lists;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 直播首页直播列表
     * @param  int  $uid
     * @return array
     */
    public function getRecommendLive($uid=0)
    {
        $liveLists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at',
                'playback_price', 'is_free', 'password')
            ->where('status', 4)
            ->orderBy('begin_at')
            ->limit(3)
            ->get()
            ->toArray();
        if ( ! empty($liveLists)) {
            foreach ($liveLists as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                    $v['live_status'] = 1;
                } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                    $v['live_status'] = 3;
                } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                    $v['live_status'] = 2;
                }
                $isSub = Subscribe::isSubscribe($uid, $v['id'], 3);
                $v['is_sub'] = $isSub ?? 0;

                $isAdmin = LiveConsole::isAdmininLive($uid, $v['id']);
                $v['is_admin'] = $isAdmin ? 1 : 0;
                $v['info_id'] = $channel->id;
                $v['is_password'] = $v['password'] ? 1 : 0;
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }
        return $liveLists;
    }
}
