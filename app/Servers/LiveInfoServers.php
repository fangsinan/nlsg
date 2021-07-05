<?php


namespace App\Servers;


use App\Models\BackendLiveRole;
use App\Models\Live;
use Illuminate\Support\Facades\DB;

class LiveInfoServers
{
    public function liveSubOrder($params)
    {
        $size = $params['size'] ?? 10;

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $query = DB::table('nlsg_subscribe as s')
            ->join('nlsg_user as u', 's.user_id', '=', 'u.id')
            ->leftJoin('nlsg_live_count_down as cd', function ($query) use ($live_id) {
                $query->on('cd.user_id', '=', 's.user_id')->where('cd.live_id', '=', $live_id);
            })
            ->leftJoin('nlsg_user as tu', 'tu.id', '=', 'cd.new_vip_uid')
            ->leftJoin('nlsg_backend_live_role as lr', 'tu.id', '=', 'lr.son_id')
            ->where('s.type', '=', 3)
            ->where('s.relation_id', '=', $live_id);

        //筛选
        //用户昵称 推荐人昵称
        //用户id 推荐人id
        //用户手机 推荐人手机
        if (!empty($params['nickname'] ?? '')) {
            $query->where('u.nickname', 'like', '%' . $params['nickname'] . '%');
        }
        if (!empty($params['id'] ?? '')) {
            $query->where('u.id', '=', $params['id']);
        }
        if (!empty($params['phone'] ?? '')) {
            $query->where('u.phone', 'like', '%' . $params['phone'] . '%');
        }

        if (!empty($params['t_nickname'] ?? '')) {
            $query->where('tu.nickname', 'like', '%' . $params['t_nickname'] . '%');
        }
        if (!empty($params['t_user_id'] ?? '')) {
            $query->where('tu.user_id', '=', $params['tu_user_id']);
        }
        if (!empty($params['t_phone'] ?? '')) {
            $query->where('tu.phone', 'like', '%' . $params['t_phone'] . '%');
        }

        if (!empty($params['son_flag'] ?? '')) {
            $query->where('son_flag', 'like', '%' . $params['son_flag'] . '%');
        }

        $query->orderBy('s.created_at', 'desc')
            ->select([
                's.id', 's.user_id', 'u.phone', 'u.nickname', 'tu.id as t_user_id', 'tu.phone as t_phone',
                'tu.nickname as t_nickname', 'lr.son_flag', 's.created_at', 's.relation_id'
            ]);

        return $query->paginate($size);
    }

    public function liveOrder($params)
    {
        $size = $params['size'] ?? 10;

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $twitter_id_list = $this->twitterIdList('', $check_live_id->user_id);
        return $twitter_id_list;

    }

    public function comment($params)
    {

    }

    public function orderOnlineNum($params)
    {

    }

    public function onlineNum($params)
    {

    }

    //获得名下所有渠道的user_id
    public function twitterIdList($phone = '', $user_id = '')
    {
        return BackendLiveRole::where(function ($query) use ($phone, $user_id) {
            $query->where('parent', '=', $phone)
                ->orWhere('parent_id', '=', $user_id);
        })->pluck('son_id')->toArray();
    }

}
