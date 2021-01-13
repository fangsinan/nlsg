<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/17
 * Time: 2:01 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class LiveInfo extends Model
{
    protected $table = 'nlsg_live_info';


    public function live()
    {
        return $this->belongsTo(Live::class, 'live_pid', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 直播首页回放列表
     * @param  int  $uid
     * @return array
     */
    public function getBackLists($uid = 0)
    {

        $cache_live_name = 'live_back_list';
        $lists = Cache::get($cache_live_name);
        if (empty($liveLists)) {
            $lists = LiveInfo::with('user:id,nickname',
                'live:id,title,describe,price,cover_img,begin_at,type,playback_price,is_free,password')
                ->select('id', 'live_pid', 'user_id')
                ->where('status', 1)
                ->where('playback_url', '!=', '')
                ->orderBy('begin_at', 'desc')
                ->limit(2)
                ->get()
                ->toArray();
            $expire_num = CacheTools::getExpire('live_back_list');
            Cache::put($cache_live_name, $lists, $expire_num);
        }

        if ( ! empty($lists)) {
            $backLists = [];
            foreach ($lists as &$v) {
                $isSub = Subscribe::isSubscribe($uid, $v['live_pid'], 3);
                $isAdmin = LiveConsole::isAdmininLive($uid, $v['live_pid']);
                $backLists[] = [
                    'id'             => $v['live']['id'],
                    'title'          => $v['live']['title'],
                    'is_password'    => $v['live']['password'] ? 1 : 0,
                    'describe'       => $v['live']['describe'],
                    'price'          => $v['live']['price'],
                    'cover_img'      => $v['live']['cover_img'],
                    'playback_price' => $v['live']['playback_price'],
                    'live_time'      => date('Y.m.d H:i', strtotime($v['live']['begin_at'])),
                    'is_free'        => $v['live']['is_free'],
                    'info_id'        => $v['id'],
                    'is_sub'         => $isSub ?? 0,
                    'is_admin'       => $isAdmin ? 1 : 0
                ];
            }
        }

        return $lists;


    }

}
