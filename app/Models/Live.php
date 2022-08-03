<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
class Live extends Base
{
    protected $table = 'nlsg_live';

    protected $fillable = ['user_id', 'cover_img', 'title', 'describe', 'price', 'twitter_money', 'begin_at',
        'end_at', 'helper','is_free','content','need_virtual','need_virtual_num','steam_end_time',
                           'steam_begin_time','classify','valid_time_range'];
    public function getIndexLive($ids)
    {
        if (!$ids) {
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
     * @param int $uid
     * @return array
     */
    public function getRecommendLive($uid = 0)
    {
//        $cache_live_name = 'live_index_list';
//        $liveLists = Cache::get($cache_live_name);
//        if (empty($liveLists)) {
        $testers = explode(',', ConfigModel::getData(35, 1));
        $user = User::where('id', $uid)->first();

        $query = Live::query();
        if (!$uid || ($user && !in_array($user->phone, $testers))) {
            $query->where('is_test', '=', 0);
        } else {
            $query->whereIn('is_test', [0, 1]);
        }

        $liveLists = $query->with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at','steam_begin_time',
                'playback_price', 'is_free', 'password')
            ->where('status', 4)
            ->where('is_finish', 0)
            ->where('is_del', 0)
            ->orderBy('begin_at')
            ->limit(3)
            ->get()
            ->toArray();
//            $expire_num = CacheTools::getExpire('live_index_list');
//            Cache::put($cache_live_name, $liveLists, $expire_num);
//        }

        if (!empty($liveLists)) {
            foreach ($liveLists as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($channel) {
                    if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                        $v['live_status'] = 1;
                    } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                        $v['live_status'] = 3;
                    } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                        $v['live_status'] = 2;
                    }
                    $v['info_id'] = $channel->id;
                }
                $isSub = Subscribe::isSubscribe($uid, $v['id'], 3);
                $v['is_sub'] = $isSub ?? 0;

                $isAdmin = LiveConsole::isAdmininLive($uid, $v['id']);
                $v['is_admin'] = $isAdmin ? 1 : 0;

                $v['is_password'] = $v['password'] ? 1 : 0;
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }
        return $liveLists;
    }


    //Route::get('live/send_test', 'LiveController@test');
    public static function sendLiveCountDown()
    {

        $flag = true;
        $title = "经营家庭和孩子的秘密";
        $size = 100;
        $live_id = 1;


//        $live_data = Live::where(['id' => $live_id, 'status' => 4])->first();
//        if (time() < (strtotime($live_data['begin_at']) - 600)) {
//            return;
//        }

        while ($flag) {
            $phone = [];
            $params = [];
            $name = [];
            $up_where = [];
            $user_phone = LiveCountDown::select('id', 'phone')->where([
                'live_id' => $live_id, 'is_send' => 0
            ])->limit($size)->get()->toArray();

            if (!empty($user_phone)) {
                foreach ($user_phone as $key => $val) {
                    if ($val['phone']) {
                        $phone[] = $val['phone'];
                        $params[$key]['name'] = $title;
                        $name[] = '能量时光';
                        //改状态
                        $up_where[] = $val['id'];


                        //发送短信
                        $easySms = app('easysms');
                        $result = $easySms->send($val['phone'], [
                            'template' => 'SMS_168311509',
                            'data' => ['name' => $title],
                        ], ['aliyun']);

                    }
                }
                if (!empty($up_where)) {
                    LiveCountDown::whereIn('id', $up_where)->update(['is_send' => 1]);
                }
            } else {
                $flag = false;
            }

        }
        return $flag;

    }

    public function liveInfo()
    {
        return $this->hasOne(LiveInfo::class, 'live_pid', 'id');
    }

    public function livePoster()
    {
        return $this->hasMany(LivePoster::class, 'live_id', 'id');
    }

    public static function teamInfo($team_id = 0, $only_not_start = 1,$first=1)
    {
        $now_date = date('Y-m-d H:i:s');

        $query = self::where('team_id', '=', $team_id)->where('status', '=', 4);

        if ($only_not_start == 1) {
            $query->where('team_end_time', '>', $now_date);
        }

        $query->with(['liveInfo:id,live_pid','user:id,nickname']);
        $query->orderBy('team_begin_time', 'asc')->orderBy('id', 'asc');
        $query->select([
            'id', 'title','user_id','order_num','describe','is_free',
            'cover_img','teacher_img','banner_img','price',
            'team_id',DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(begin_at),\'%Y-%m-%d %H:%i\') as begin_at'),
            'team_begin_time', 'team_end_time'
        ]);

        if ($first == 1){
            $query->limit(1);
        }

        return $query->get();

    }

    static function search($keywords)
    {
        $res = Live::select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at', 'user_id', 'price', 'created_at')
            ->where('status', 4)
            ->where('is_del', 0)
            ->where('is_test', 0)
            ->where('begin_at', '>=',date('Y-m-d ', time()))
            ->with(['user:id,nickname'])
//            ->where(function ($query) use ($keywords) {
//                $query->orWhere('title', 'LIKE', "%$keywords%");
//                $query->orWhere('describe', 'LIKE', "%$keywords%");
//            })
            ->get();


        foreach ($res as &$v) {
            $channel = LiveInfo::where('live_pid', $v['id'])
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->first();
            if ($channel) {
                if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                    $v['live_status'] = 1;
                } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                    $v['live_status'] = 3;
                } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                    $v['live_status'] = 2;
                }
                $v['info_id'] = $channel->id;
            }
//            $isSub = Subscribe::isSubscribe($uid, $v['id'], 3);
//            $v['is_sub'] = $isSub ?? 0;

//            $isAdmin = LiveConsole::isAdmininLive($uid, $v['id']);
//            $v['is_admin'] = $isAdmin ? 1 : 0;

//            $v['is_password'] = $v['password'] ? 1 : 0;
//            $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
        }

        return ['res' => $res, 'count' => $res->count()];
    }
}
