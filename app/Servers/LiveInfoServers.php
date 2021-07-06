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
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
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
        if (!empty($params['user_id'] ?? '')) {
            $query->where('u.id', '=', $params['user_id']);
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

        $query->orderBy('s.created_at', 'desc');

        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            $query->select([
                's.id', 's.user_id', 'u.phone', 'u.nickname', 'tu.id as t_user_id', 'tu.phone as t_phone',
                'tu.nickname as t_nickname', 'lr.son_flag', 's.created_at', 's.relation_id'
            ]);
            return $query->paginate($size);
        } else {
            $query->select([
                's.user_id', 'u.phone', 'u.nickname', 'tu.id as t_user_id', 'tu.phone as t_phone',
                'tu.nickname as t_nickname', 'lr.son_flag', 's.created_at', 's.relation_id'
            ]);
            return $query->get();
        }
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

        $query = DB::table('nlsg_order as o')
            ->join('nlsg_live as l', 'o.live_id', '=', 'l.id')
            ->leftJoin('nlsg_live_count_down as cd', function ($query) use ($live_id) {
                $query->on('cd.user_id', '=', 'o.user_id')->where('cd.live_id', '=', $live_id);
            })
            ->leftJoin('nlsg_user as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('nlsg_user as lt', 'lt.id', '=', 'o.twitter_id')
            ->leftJoin('nlsg_backend_live_role as lr', 'lt.id', '=', 'lr.son_id');

        //搜索
        //用户昵称,手机 推荐人手机,昵称,flag
        if (!empty($params['nickname'] ?? '')) {
            $query->where('u.nickname', 'like', '%' . $params['t_nickname'] . '%');
        }

        if (!empty($params['phone'] ?? '')) {
            $query->where('u.phone', 'like', '%' . $params['t_phone'] . '%');
        }

        if (!empty($params['t_nickname'] ?? '')) {
            $query->where('lt.nickname', 'like', '%' . $params['t_nickname'] . '%');
        }

        if (!empty($params['t_phone'] ?? '')) {
            $query->where('lt.phone', 'like', '%' . $params['t_phone'] . '%');
        }

        if (!empty($params['son_flag'] ?? '')) {
            $query->where('son_flag', 'like', '%' . $params['son_flag'] . '%');
        }


        $query->whereIn('o.twitter_id', $twitter_id_list);

//        $query->where('o.live_id', '=', $live_id);
        $query->whereRaw('o.type=10 and o.`status`=1 and u.is_test_pay=0
            and o.pay_price>=0.1 and o.is_shill=0 and o.activity_tag <>\'cytx\'')
            ->groupBy('o.id')
            ->orderBy('o.id', 'desc');


        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            $query->select([
                'o.user_id', 'u.phone', 'u.nickname', 'o.twitter_id',
                'lt.phone as t_phone', 'lt.nickname as t_nickname', 'lr.son_flag',
                'pay_price', 'pay_time', 'o.live_id', 'l.title as live_title',
                'o.id as order_id', 'o.pay_type', 'os_type',
                'cd.new_vip_uid', 'activity_tag', 'cd.id as cd_id'
            ]);
            return $query->paginate($size);
        } else {
            $query->select([
                'o.user_id', 'u.phone', 'u.nickname', 'o.twitter_id',
                'lt.phone as t_phone', 'lt.nickname as t_nickname', 'lr.son_flag',
                'pay_price', 'pay_time', 'o.live_id', 'l.title as live_title',
            ]);
            return $query->get();
        }

    }

    public function comment($params)
    {

    }

    public function orderOnlineNum($params)
    {

    }

    public function onlineNum($params)
    {
        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        return DB::table('nlsg_live_online_user')
            ->where('live_id', '=', $live_id)
            ->groupBy(Db::raw('left(online_time,16)'))
            ->orderBy('online_time')
            ->select([
                DB::raw('count(*) as counts'),
                DB::raw('LEFT(online_time,16) as time')
            ])
            ->get();
    }

    public function onlineNumInfo($params)
    {
        $size = $params['size'] ?? 10;
        $live_id = $params['live_id'] ?? 0;
        $date = $params['date'] ?? '';
        if (empty($date)) {
            return ['code' => false, 'msg' => '时间错误'];
        }

        $begin_time = date('Y-m-d H:i:00', strtotime($date));
        $end_time = date('Y-m-d H:i:59', strtotime($date));

        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $query = DB::table('nlsg_live_online_user as lou')
            ->join('nlsg_live_count_down as cd', function ($query) use ($live_id) {
                $query->on('cd.user_id', '=', 'lou.user_id')->where('cd.live_id', '=', $live_id);
            })
            ->leftJoin('nlsg_user as u', 'u.id', '=', 'cd.new_vip_uid');

        $query->where('lou.live_id', '=', $live_id)
            ->whereBetween('lou.online_time', [$begin_time, $end_time])
            ->groupBy('lou.user_id');

        $query->select([
            'lou.user_id', 'cd.phone', 'cd.new_vip_uid as t_user_id', 'u.phone as t_phone',
            'u.nickname as t_nickname', DB::raw('left(lou.online_time,16) as online_time')
        ]);

        return $query->paginate($size);
    }

    public function userWatch($params)
    {
        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $size;

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $flag = $params['flag'] ?? '';
        if (!in_array($flag, [1, 2])) {
            return ['code' => false, 'msg' => 'flag参数错误'];
        }

        if ($flag == 1) {
            $where_str = 'EXISTS';
        } else {
            $where_str = 'NOT EXISTS';
        }

        $sql = "
        SELECT
            s.id,
            s.user_id,
            u.phone ,s.created_at,lr.son,lr.son_flag
        FROM
            nlsg_subscribe AS s
            JOIN nlsg_user AS u ON s.user_id = u.id
            LEFT JOIN nlsg_live_count_down as cd on s.user_id = cd.user_id and cd.live_id = $live_id
            LEFT JOIN nlsg_backend_live_role as lr on cd.new_vip_uid = lr.son_id
        WHERE
            ( s.order_id > 9 OR s.channel_order_id > 0 )
            AND s.relation_id = $live_id
            AND s.type = 3
            AND $where_str ( SELECT id FROM nlsg_live_online_user lou WHERE lou.user_id = s.user_id AND lou.live_id = $live_id )
        limit $size offset $offset
        ";

        return DB::select($sql);

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
