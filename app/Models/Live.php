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
        $list = $this->select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at','price','order_num')
            ->whereIn('id', $ids)
            ->where('is_del', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($list) {
            $channel = LiveInfo::where('live_pid', $list->id)
                        ->where('status', 1)
                        ->orderBy('id','desc')
                        ->first();
            $list['info_id'] =  $channel->id;
        }
        $sclass = new \StdClass();
        return $list ?: $sclass ;
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
