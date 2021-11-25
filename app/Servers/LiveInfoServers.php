<?php


namespace App\Servers;


use App\Models\BackendLiveRole;
use App\Models\CacheTools;
use App\Models\Live;
use App\Models\LiveDeal;
use App\Models\LiveInfo;
use App\Models\LiveLogin;
use App\Models\LiveOnlineUser;
use App\Models\LiveSonFlagPoster;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LiveInfoServers
{
    public function liveSubOrder($params)
    {
        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;

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
        if (!empty($params['user_id'] ?? '')) {
            $query->where('u.id', '=', $params['user_id']);
        }
        if (!empty($params['phone'] ?? '')) {
            $query->where('u.phone', 'like', '%' . $params['phone'] . '%');
        }
        if (!empty($params['t_user_id'] ?? '')) {
            $query->where('tu.user_id', '=', $params['t_user_id']);
        }
        if (!empty($params['t_phone'] ?? '')) {
            $query->where('tu.phone', 'like', '%' . $params['t_phone'] . '%');
        }

        if (!empty($params['son_flag'] ?? '')) {
            $query->where('son_flag', 'like', '%' . $params['son_flag'] . '%');
        }

        $query->orderBy('s.created_at', 'desc');
//        $query->groupBy('s.user_id');

        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            $query->select([
                's.id', 's.user_id', 'u.phone', 'u.nickname', 'tu.id as t_user_id', 'tu.phone as t_phone',
                DB::raw('if(tu.internal_remarks = "",tu.nickname,tu.internal_remarks) as t_nickname'),
                'lr.son_flag', 's.created_at', 's.relation_id'
            ]);
            $res    = $query->paginate($size);
            $custom = collect(['live_user_id' => $check_live_id->user_id]);
            return $custom->merge($res);
        }

        $query->select([
            's.user_id', DB::raw("CONCAT('`',u.phone) as phone"), 'u.nickname', 'tu.id as t_user_id',
            DB::raw("CONCAT('`',tu.phone) as t_phone"),
            DB::raw('if(tu.internal_remarks = "",tu.nickname,tu.internal_remarks) as t_nickname'),
            'lr.son_flag', 's.created_at', 's.relation_id'
        ]);
        return $query->limit($size)->offset(($page - 1) * $size)->get();

    }

    public function liveOrderKun($params, $admin)
    {
        $size       = $params['size'] ?? 10;
        $query_flag = $params['query_flag'] ?? '';

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
//        if ($check_live_id->user_id != 161904) {
//            return ['code' => false, 'msg' => '不是王琨的直播间'];
//        }
        if (!in_array($check_live_id->user_id, [161904, 185689], true)) {
            return ['code' => false, 'msg' => '直播间归属错误'];
        }

        $temp_begin_order = LiveDeal::query()
            ->where('live_id', '=', $live_id)
            ->value('ordernum');


        $query = DB::table('nlsg_live_deal as ld')
            ->join('nlsg_order as o', 'ld.ordernum', '=', 'o.ordernum')
            ->where('o.is_shill', '=', 0);

        $admin_live_role = (int)($admin['live_role'] ?? 0);
        if (in_array($admin_live_role, [21, 23])) {
            $query->where('ld.type', '<>', 8);
        }

        if (!empty($temp_begin_order)) {
            $temp_begin_order_id = Order::query()
                ->where('ordernum', '=', $temp_begin_order)
                ->value('id');
            if (!empty($temp_begin_order_id)) {
                $query->where('o.id', '>=', $temp_begin_order_id);
            }
        }

        $query->where('ld.live_id', '=', $live_id);

//        $query->where('ld.pay_time','>=','2021-09-26 00:00:00')
//            ->where('ld.pay_time','<','2021-10-27 00:00:00')
//            ->where('ld.pay_price','=',1980);

        if (!empty($params['ordernum'] ?? '')) {
            $query->where('ld.ordernum', 'like', '%' . $params['ordernum'] . '%');
        }
        if (!empty($params['phone'] ?? '')) {
            $query->where('ld.phone', 'like', '%' . $params['phone'] . '%');
        }
        if (!empty($params['invite_phone'] ?? '')) {
            $query->where('ld.invite_phone', 'like', '%' . $params['invite_phone'] . '%');
        }
        if (!empty($params['protect_phone'] ?? '')) {
            $query->where('ld.protect_phone', 'like', '%' . $params['protect_phone'] . '%');
        }
        if (!empty($params['diamond_phone'] ?? '')) {
            $query->where('ld.diamond_phone', 'like', '%' . $params['diamond_phone'] . '%');
        }
        if (!empty($params['qd_name'] ?? '')) {
            switch ($params['qd_name']) {
                case '抖音':
                    $params['qd'] = 1;
                    break;
                case '李婷':
                    $params['qd'] = 2;
                    break;
                default:
                    $params['qd'] = 0;
            }
        }
        if (!empty($params['qd'] ?? '')) {
            $query->where('ld.qd', '=', $params['qd']);
        }

        $params['type'] = 0;
        if (!empty($params['type_name'] ?? '')) {
            switch ($params['type_name']) {
                case '经营能量门票':
                    $params['type'] = 1;
                    break;
                case '一代天骄门票':
                    $params['type'] = 2;
                    break;
                case '演说能量门票':
                    $params['type'] = 3;
                    break;
                case '经营能量 360套餐':
                    $params['type'] = 4;
                    break;
                case '30天智慧父母(亲子)训练营':
                    $params['type'] = 5;
                    break;
                case '学业规划训练营':
                    $params['type'] = 6;
                    break;
                case '':
                    $params['type'] = 0;
                    break;
                default:
                    $params['type'] = 999;
            }
        }

        if (!empty($params['type'])) {
            $query->where('ld.type', '=', $params['type']);
        }

        switch ($query_flag) {
            case 'money_sum':
                return $query->sum('ld.pay_price');
            case 'user_sum':
                return $query->count('ld.user_id');
        }

        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            $query->select([
                'ld.ordernum', 'ld.pay_price', 'ld.num', 'ld.pay_time',
                DB::raw('(case ld.type when 1 then "经营能量门票" when 2 then "一代天骄门票" when 3 then "演说能量门票"
            when 4 then "经营能量+360套餐" when 5 then "30天智慧父母(亲子)训练营" when 6 then "学业规划训练营"
            when 7 then "30天智慧父母(亲子)训练营-直播专享" when 8 then "抖音直播内部教材" else "--" end) as type_name'),
                'ld.phone', 'ld.nickname', 'ld.user_id',
                DB::raw('(case ld.identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as identity_name'),
                'ld.invite_phone', 'ld.invite_nickname',
                'ld.protect_user_id', 'ld.protect_phone', 'ld.protect_nickname',
                DB::raw('(case ld.protect_identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as protect_identity_name'),
                'ld.profit_user_id', 'ld.profit_price',
                'ld.diamond_user_id', 'ld.diamond_phone', 'ld.diamond_nickname',
                DB::raw('(case ld.diamond_identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as diamond_identity'),
                DB::raw('(case ld.is_tiktok when 1 then "是"  else "否" end) as is_tiktok'),
                'ld.tiktok_ordernum', 'ld.tiktok_time', 'ld.qd',
                DB::raw('(case ld.qd when 1 then "抖音" when 2 then "李婷" when 3 then "自有" when 4 then "地推" else "--" end) as qd_name'), //1 抖音 2 李婷 3 自有平台 4地推
                'ld.sub_live_id', 'ld.sub_live_pay_price', 'ld.sub_live_pay_time',
                DB::raw('(case o.is_shill when 1 then "是"  else "否" end) as is_refund'),
            ]);
            $res = $query->paginate($size);
        } else {
            $query->select([
                DB::raw("CONCAT('`',ld.ordernum) as ordernum"),
                'ld.pay_price', 'ld.num', 'ld.pay_time',
                DB::raw('(case ld.type when 1 then "经营能量门票" when 2 then "一代天骄门票" when 3 then "演说能量门票"
            when 4 then "经营能量+360套餐" when 5 then "30天智慧父母(亲子)训练营" when 6 then "学业规划训练营"
            when 7 then "30天智慧父母(亲子)训练营-直播专享" when 8 then "抖音直播内部教材" else "类型错误" end) as type_name'),
                DB::raw("CONCAT('`',ld.phone) as phone"), 'ld.nickname', 'ld.user_id',
                DB::raw('(case ld.identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as identity_name'),
                DB::raw("CONCAT('`',ld.invite_phone) as invite_phone"), 'invite_nickname',
                'ld.protect_user_id', 'ld.protect_phone', 'ld.protect_nickname',
                DB::raw('(case ld.protect_identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as protect_identity_name'),
                'ld.profit_user_id', 'ld.profit_price',
                'ld.diamond_user_id',
                DB::raw("CONCAT('`',ld.diamond_phone) as diamond_phone"), 'ld.diamond_nickname',
                DB::raw('(case ld.diamond_identity when 1 then "幸福大师" when 2 then "钻石经销商" else "--" end) as diamond_identity'),
                DB::raw('(case ld.is_tiktok when 1 then "是"  else "否" end) as is_tiktok'),
                'ld.tiktok_ordernum', 'ld.tiktok_time', 'qd',
                DB::raw('(case ld.qd when 1 then "抖音" when 2 then "李婷" when 3 then "自有" when 4 then "地推" else "--" end) as qd_name'),
                'ld.sub_live_id', 'ld.sub_live_pay_price', 'ld.sub_live_pay_time',
                DB::raw('(case o.is_shill  when 1 then "是"  else "否" end) as is_refund'),
            ]);
            $res = $query->get();
        }

        if ($res !== null) {
            $UserId_Arr = [];
            foreach ($res as $k => $v) {
                $UserId_Arr[] = $v->user_id;
            }
            $UserArr = DB::table('nlsg_user as U')
                ->select(['U.id', 'U.unionid', 'N.qw_name', 'W.follow_user_userid'])
                ->leftjoin('nlsg_user_wechat as W', function ($query) {
                    $query->on('W.unionid', '=', 'U.unionid')->where('W.unionid', '<>', '');
                })
                ->leftJoin('nlsg_user_wechat_name as N', 'N.follow_user_userid', '=', 'W.follow_user_userid')
                ->whereIn('U.id', $UserId_Arr)->get();

            $address_query = DB::table('nlsg_mall_address as ma')
                ->join('nlsg_area as a1', 'ma.province', '=', 'a1.id')
                ->leftJoin('nlsg_area as a2', 'ma.city', '=', 'a2.id')
                ->leftJoin('nlsg_area as a3', 'ma.area', '=', 'a3.id')
                ->where('ma.is_default', '=', 1)
                ->where('ma.is_del', '=', 0)
                ->whereIn('ma.user_id', $UserId_Arr)
                ->select([
                    'ma.id', 'ma.user_id', 'ma.name', 'ma.phone', 'a1.name as province_name', 'a2.name as city_name',
                    'a3.name as area_name', 'ma.details'
                ])
                ->get();

            $UnionArr = [];
            foreach ($UserArr as $key => $val) {
                $UnionArr[$val->id] = [
                    'qw_name'            => (empty($val->qw_name)) ? '' : $val->qw_name,
                    'follow_user_userid' => (empty($val->follow_user_userid)) ? '' : $val->follow_user_userid
                ];
            }

            foreach ($res as $k => $v) {
                $v->unionid            = $UnionArr[$v->user_id]['qw_name'];
                $v->follow_user_userid = $UnionArr[$v->user_id]['follow_user_userid'];
                $v->address            = '';
                $v_address_default     = '';
                $v_address_phone       = '';
                foreach ($address_query as $aq_v) {
                    if ($v->user_id === $aq_v->user_id) {
                        if (!empty($aq_v->name)) {
                            $v_address_phone .= ' ' . $aq_v->name;
                        }
                        if (!empty($aq_v->phone)) {
                            $v_address_phone .= ' ' . $aq_v->phone;
                        }
                        if (!empty($aq_v->province_name)) {
                            $v_address_default .= ' ' . $aq_v->province_name;
                        }
                        if (!empty($aq_v->city_name)) {
                            $v_address_default .= ' ' . $aq_v->city_name;
                        }
                        if (!empty($aq_v->area_name)) {
                            $v_address_default .= ' ' . $aq_v->area_name;
                        }
                        if (!empty($aq_v->details)) {
                            $v_address_default .= ' ' . $aq_v->details;
                        }
                    }
                }
                $v_address_default = trim($v_address_default);
                $v_address_phone   = trim($v_address_phone);
                if (!empty($v_address_phone)) {
                    $v->address .= $v_address_phone;
                }
                if (!empty($v_address_default)) {
                    $v->address .= '(' . $v_address_default . ')';
                }

            }
        }

        if (empty($excel_flag)) {
            $custom = collect(['live_user_id' => $check_live_id->user_id]);
            return $custom->merge($res);
        }

        return $res;

    }

    public function liveOrder($params, $user)
    {
        $size       = $params['size'] ?? 10;
        $query_flag = $params['query_flag'] ?? '';

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::query()->where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        if ($user['role_id'] === 1) {
            $twitter_id_list = null;
        } else {
            $twitter_id_list = $this->twitterIdList($user['username']);
        }

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
            $query->where('u.nickname', 'like', '%' . $params['nickname'] . '%');
        }

        if (!empty($params['phone'] ?? '')) {
            $query->where('u.phone', 'like', '%' . $params['phone'] . '%');
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

        if (!empty($params['ordernum'] ?? '')) {
            $query->where('o.ordernum', 'like', '%' . $params['ordernum'] . '%');
        }

        if (!empty($params['pay_price'] ?? '')) {
            $query->where('pay_price', '=', $params['pay_price']);
        }


        if ($user['role_id'] === 1) {
            $temp_live_time_begin = date('Y-m-d 00:00:00', strtotime($check_live_id->begin_at));
            $temp_live_time_end   = date('Y-m-d 23:59:59', strtotime($check_live_id->end_at));
            $temp_order_begin_id  = Order::query()
                ->where('type', '=', 10)
                ->where('status', '=', 1)
                ->where('pay_time', '>=', $temp_live_time_begin)
                ->value('id');
            if (empty($temp_order_begin_id)) {
                $temp_order_begin_id = Order::query()->max('id');
            }
            $query->where('o.id', '>=', $temp_order_begin_id)
                ->where('o.created_at', '<=', $temp_live_time_end);
        } else {
            $query->where('o.remark', '=', $live_id);
        }


        if ($twitter_id_list !== null) {
            $query->whereIn('o.twitter_id', $twitter_id_list);
        }

//        $query->where('o.live_id', '=', $live_id);
        $query->whereRaw('o.type=10 and o.`status`=1 and u.is_test_pay=0
            and o.pay_price>=0.1 and o.is_shill=0 and o.activity_tag <>\'cytx\'');


        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            switch ($query_flag) {
                case 'money_sum':
                    return $query->sum('pay_price');
                case 'user_sum':
                    return $query->count('o.user_id');
            }

            $query
                ->groupBy('o.id')
                ->orderBy('o.id', 'desc')
                ->select([
                    'o.user_id', 'u.phone', 'u.nickname', 'o.twitter_id',
                    'lt.phone as t_phone',
//                    'lt.nickname as t_nickname',
                    DB::raw('if(lt.internal_remarks = "",lt.nickname,lt.internal_remarks) as t_nickname'),
                    'lr.son_flag',
                    'pay_price', 'pay_time', 'o.live_id', 'l.title as live_title',
                    'o.id as order_id', 'o.pay_type', 'os_type',
                    'cd.new_vip_uid', 'activity_tag', 'cd.id as cd_id', 'o.ordernum', 'o.remark'
                ]);
            $res = $query->paginate($size);

        } else {
            $query->groupBy('o.id')
                ->orderBy('o.id', 'desc')
                ->select([
                    DB::raw("CONCAT('`',o.ordernum) as ordernum"),
                    'o.user_id', DB::raw("CONCAT('`',u.phone) as phone"), 'u.nickname', 'o.twitter_id',
                    DB::raw("CONCAT('`',lt.phone) as t_phone"),
//                    'lt.nickname as t_nickname',
                    DB::raw('if(lt.internal_remarks = "",lt.nickname,lt.internal_remarks) as t_nickname'),
                    'lr.son_flag',
                    'pay_price', 'pay_time', 'o.live_id', 'l.title as live_title', 'o.remark'
                ]);
            $res = $query->get();
        }

        foreach ($res as &$v) {
            //修改源直播名称
            if ($v->remark == $check_live_id->id) {
                $v->source_live_id    = $check_live_id->id;
                $v->source_live_title = $check_live_id->title;
            } elseif (empty($v->remark)) {
                $v->source_live_id = $v->source_live_title = '';
            } else {
                $v->source_live_id    = $v->remark;
                $v->source_live_title = Live::query()->where('id', '=', $v->remark)->value('title');
            }
            unset($v->remark);
        }

        if (empty($excel_flag)) {
            $custom = collect(['live_user_id' => $check_live_id->user_id]);
            return $custom->merge($res);
        }
        return $res;
    }

    public function onlineNum($params, $user)
    {
        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        //获取所有渠道

        $table_name     = 'nlsg_live_online_user';
        $cache_key_name = 'son_flag_' . $check_live_id->user_id;
        $expire_num     = CacheTools::getExpire('son_flag');
        $son_flag       = Cache::get($cache_key_name);

        if (empty($son_flag)) {

            if ($user['role_id'] === 1 || ($user['user_id'] === 169209)) {
                $check_live_id->user_id = 169209;
            }

            $son_flag = BackendLiveRole::where('parent_id', '=', $check_live_id->user_id)
                ->where('status', '=', 1)
                ->select(['son', 'son_id', 'son_flag'])
                ->orderBy('sort', 'asc')   //按标记排序
                ->get();
            Cache::put($cache_key_name, $son_flag, $expire_num);
        }
        $res['son_flag'] = $son_flag;

        $son_id = (int)($params['son_id'] ?? 0);

        //特定渠道
        if (!empty($son_id)) {
            //观看时常大于30分钟的
            //累计人次login
            $res['total_login'] = LiveLogin::where('live_id', '=', $live_id)
                ->where('live_son_flag', $son_id)
                ->count();
            //累计人数sub
            $order_num_sql = "
            SELECT
                count(*) AS counts
            FROM
                (
                    SELECT
                        id
                    FROM  nlsg_subscribe
                    WHERE  relation_id = $live_id and type=3  AND STATUS = 1 AND twitter_id =$son_id
                    GROUP BY user_id
                ) AS a";

            $res['total_sub'] = DB::select($order_num_sql)[0]->counts;

            if ($live_id >= 139) {
                $list_sql = "SELECT online_time_str as time,counts from nlsg_live_online_user_counts where live_id = $live_id and live_son_flag = $son_id";
            } else {
                $list_sql = "SELECT
	online_time_str as time,
	count(*) as counts
FROM
	$table_name
WHERE
	live_id = $live_id and live_son_flag = $son_id
GROUP BY
	online_time_str
	ORDER BY online_time_str asc";
            }

        } else { //总数据
            //累计人次login
            $res['total_login'] = LiveLogin::where('live_id', '=', $live_id)->count();
            //累计人数sub
            $order_num_sql = "
            SELECT
                count(*) AS counts
            FROM
                (
                SELECT
                    id
                FROM nlsg_subscribe
                WHERE relation_id = $live_id and type=3 AND STATUS = 1
                GROUP BY user_id
                ) AS a";

            $res['total_sub'] = DB::select($order_num_sql)[0]->counts;
            //在线人数
            if ($live_id >= 139) {
                $list_sql = "SELECT
	online_time_str AS time,
	sum(counts) as counts
FROM
	nlsg_live_online_user_counts
WHERE
	live_id = $live_id
	GROUP BY online_time_str";
            } else {
                $list_sql = "SELECT
	online_time_str as time,
	count(*) as counts
FROM
	$table_name
WHERE
	live_id = $live_id
GROUP BY
	online_time_str";
            }
        }

        //在线人数折线图
        $res['list'] = DB::select($list_sql);

        if (($params['only_list'] ?? 0) == 1) {
            return $res['list'];
        }

        $img_data            = [];
        $img_data['columns'] = [
            '时间', '人数'
        ];
        $img_data['rows']    = [];
        foreach ($res['list'] as $v) {
            $temp_img_data = [];
//            $temp_img_data['时间'] = $v->time;substr($v->time,-11);
            $temp_img_data['时间'] = substr($v->time, -11);
            $temp_img_data['人数'] = $v->counts;
            $img_data['rows'][]  = $temp_img_data;
        }

        $res['img_data'] = $img_data;
        $res['list']     = [];

        return $res;
    }

    public function onlineNumInfo($params)
    {
        $size    = $params['size'] ?? 10;
        $live_id = $params['live_id'] ?? 0;
        $date    = $params['date'] ?? '';
        if (empty($date)) {
            $temp_data = LiveOnlineUser::where('live_id', '=', $live_id)->orderBy('id', 'desc')->first();
            $date      = $temp_data->online_time_str ?? date('Y-m-d 00:00:00');
        }

        $begin_time = date('Y-m-d H:i:00', strtotime($date));
        $end_time   = date('Y-m-d H:i:59', strtotime($date));

        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

//        $chose_table_sql = "SHOW TABLES LIKE 'nlsg_live_online_user_".$live_id."'";
//        $chose_table_res = DB::select($chose_table_sql);
//        if (empty($chose_table_res)){
        $table_name = 'nlsg_live_online_user';
//        }else{
//            $table_name = 'nlsg_live_online_user_'.$live_id;
//        }

        $query = DB::table($table_name . ' as lou')
            ->join('nlsg_user as lu', 'lou.user_id', '=', 'lu.id')
            ->leftJoin('nlsg_live_count_down as cd', function ($query) use ($live_id) {
                $query->on('cd.user_id', '=', 'lou.user_id')->where('cd.live_id', '=', $live_id);
            })
            ->leftJoin('nlsg_user as u', 'u.id', '=', 'cd.new_vip_uid');

        $query->where('lou.live_id', '=', $live_id)
            ->whereBetween('lou.online_time', [$begin_time, $end_time])
            ->groupBy('lou.user_id');


        $excel_flag = $params['excel_flag'] ?? 0;
        if (empty($excel_flag)) {
            $query->select([
                'lou.user_id', 'lu.phone', 'cd.new_vip_uid as t_user_id', 'u.phone as t_phone',
                'u.nickname as t_nickname', 'online_time_str as online_time'
            ]);
            $res    = $query->paginate($size);
            $custom = collect(['live_user_id' => $check_live_id->user_id, 'date' => $begin_time]);
            return $custom->merge($res);
        } else {
            $query->select([
                'lou.user_id',
                DB::raw("CONCAT('`',lu.phone) as phone"),
                'cd.new_vip_uid as t_user_id',
                DB::raw("CONCAT('`',u.phone) as t_phone"),
                'u.nickname as t_nickname',
                'online_time_str as online_time'
            ]);
            return $query->get();
        }

    }

    public function userWatch($params)
    {
        $size   = $params['size'] ?? 10;
        $page   = $params['page'] ?? 1;
        $offset = ($page - 1) * $size;

        $excel_flag = $params['excel_flag'] ?? 0;

        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

//        $chose_table_sql = "SHOW TABLES LIKE 'nlsg_live_online_user_".$live_id."'";
//        $chose_table_res = DB::select($chose_table_sql);
//        if (empty($chose_table_res)){
        $table_name = 'nlsg_live_online_user';
//        }else{
//            $table_name = 'nlsg_live_online_user_'.$live_id;
//        }

        $flag = $params['flag'] ?? '';
        if (!in_array($flag, [1, 2])) {
            return ['code' => false, 'msg' => 'flag参数错误'];
        }

        $phone = $params['phone'] ?? '';
        $son   = $params['son'] ?? '';

        if ($flag == 1) {
            $where_str = 'EXISTS';
        } else {
            $where_str = 'NOT EXISTS';
        }

//        $sql = "
//        SELECT
//            s.id,
//            s.user_id,
//            u.phone ,s.created_at,lr.son,lr.son_flag
//        FROM
//            nlsg_subscribe AS s
//            JOIN nlsg_user AS u ON s.user_id = u.id
//            LEFT JOIN nlsg_live_count_down as cd on s.user_id = cd.user_id and cd.live_id = $live_id
//            LEFT JOIN nlsg_backend_live_role as lr on cd.new_vip_uid = lr.son_id
//        WHERE
//            ( s.order_id > 9 OR s.channel_order_id > 0 )
//            AND s.relation_id = $live_id";

        $sql = "
        SELECT
            s.id,
            s.user_id,
            u.phone,
            s.created_at,
            t.phone as son,
            lr.son_flag
        FROM
            nlsg_subscribe AS s
            JOIN nlsg_user AS u ON s.user_id = u.id
            LEFT JOIN nlsg_user as t on s.twitter_id = t.id
            LEFT JOIN nlsg_backend_live_role AS lr ON t.id = lr.son_id
        WHERE
            ( s.order_id > 9 OR s.channel_order_id > 0 )
            AND s.relation_id = $live_id";

        if (!empty($phone)) {
            $sql .= " AND u.phone like '%$phone%' ";
        }

        if (!empty($son)) {
            $sql .= " AND lr.son like '%$son%' ";
        }

        $son_flag = $params['son_flag'] ?? '';
        if (!empty($son_flag)) {
            $sql .= " AND lr.son_flag like '%$son_flag%' ";
        }

        $sql .= "
            AND s.type = 3
            AND $where_str ( SELECT id FROM $table_name lou WHERE lou.user_id = s.user_id AND lou.live_id = $live_id )
        ";

        if (empty($excel_flag)) {
            $count_sql = "
        SELECT
           count(*) as counts
        FROM
            nlsg_subscribe AS s
            JOIN nlsg_user AS u ON s.user_id = u.id
            LEFT JOIN nlsg_user as t on s.twitter_id = t.id
            LEFT JOIN nlsg_backend_live_role AS lr ON t.id = lr.son_id
        WHERE
            ( s.order_id > 9 OR s.channel_order_id > 0 )
            AND s.relation_id = $live_id ";

            if (!empty($phone)) {
                $count_sql .= " AND u.phone like '%$phone%' ";
            }

            if (!empty($son)) {
                $count_sql .= " AND lr.son like '%$son%' ";
            }

            $son_flag = $params['son_flag'] ?? '';
            if (!empty($son_flag)) {
                $count_sql .= " AND lr.son_flag like '%$son_flag%' ";
            }

            $count_sql .= "
            AND s.type = 3
            AND $where_str ( SELECT id FROM $table_name lou WHERE lou.user_id = s.user_id AND lou.live_id = $live_id )
        ";

            $sql .= " limit $size offset $offset ";

            $list['data']         = DB::select($sql);
            $list['total']        = DB::select($count_sql)[0]->counts;
            $list['live_user_id'] = $check_live_id->user_id;
            return $list;
        }

        return DB::select($sql);

    }

    public function statistics($params, $this_user)
    {
        $live_id = $params['live_id'] ?? 0;
        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $res             = [];
        $res['user_id']  = $check_live_id->user_id;
        $res['begin_at'] = $check_live_id->begin_at;
        $res['end_at']   = $check_live_id->end_at;

        $user_info       = User::where('id', '=', $check_live_id->user_id)
            ->select(['nickname', 'headimg'])->first();
        $res['headimg']  = $user_info->headimg;
        $res['nickname'] = $user_info->nickname;
        //累计人次login 人气
        $res['live_login'] = $res['total_login'] = LiveLogin::where('live_id', '=', $live_id)->count();
        $res['order_num']  = Subscribe::query()->where('relation_id', '=', $live_id)
            ->where('type', '=', 3)
            ->where('status', '=', 1)->count();

        //累计人数sub
        $res['total_sub'] = Subscribe::where('relation_id', '=', $live_id)
            ->where('type', '=', 3)->where('status', '=', 1)->count();

        if ($check_live_id->user_id === 161904) {
            //王琨,统计live_deal
            $watch_count_sql     = "SELECT count(*) as counts from nlsg_subscribe where relation_id = $live_id and type = 3
                                                and live_watched = 1 and (order_id > 9 or channel_order_id > 0)";
            $not_watch_count_sql = "SELECT count(*) as counts from nlsg_subscribe where relation_id = $live_id and type = 3
                                                and live_watched = 0 and (order_id > 9 or channel_order_id > 0)";

            $res['watch_counts']     = DB::select($watch_count_sql)[0]->counts;
            $res['not_watch_counts'] = DB::select($not_watch_count_sql)[0]->counts;

            //王琨,统计live_deal
            $res['total_order'] = DB::table('nlsg_live_deal as ld')
                ->where('live_id', '=', $live_id)
                ->whereNotExists(function ($q) {
                    $q->from('nlsg_order as o')
                        ->where('o.ordernum', '=', 'ld.ordernum')
                        ->where('o.is_shill', '=', 1);
                })->count();

            $res['total_order_money'] = DB::table('nlsg_live_deal as ld')
                ->where('live_id', '=', $live_id)
                ->whereNotExists(function ($q) {
                    $q->from('nlsg_order as o')
                        ->where('o.ordernum', '=', 'ld.ordernum')
                        ->where('o.is_shill', '=', 1);
                })->sum('pay_price');

            $res['total_order_user'] = DB::table('nlsg_live_deal as ld')
                ->where('live_id', '=', $live_id)
                ->whereNotExists(function ($q) {
                    $q->from('nlsg_order as o')
                        ->where('o.ordernum', '=', 'ld.ordernum')
                        ->where('o.is_shill', '=', 1);
                })->count('user_id');

        } else {
            //李婷,统计order表的9.9
            $watch_count_sql     = "SELECT count(*) as counts from nlsg_subscribe where relation_id = $live_id and type = 3 and live_watched = 1";
            $not_watch_count_sql = "SELECT count(*) as counts from nlsg_subscribe where relation_id = $live_id and type = 3 and live_watched = 0";

            $res['watch_counts']     = DB::select($watch_count_sql)[0]->counts;
            $res['not_watch_counts'] = DB::select($not_watch_count_sql)[0]->counts;

            //李婷,统计order表的9.9
            $temp_order       = $this->liveOrder(['live_id' => $live_id], $this_user);
            $temp_order_money = $this->liveOrder(['live_id' => $live_id, 'query_flag' => 'money_sum'], $this_user);
            $temp_order_user  = $this->liveOrder(['live_id' => $live_id, 'query_flag' => 'user_sum'], $this_user);

            $res['total_order']       = $temp_order['total'] ?? '错误';
            $res['total_order_money'] = $temp_order_money;
            $res['total_order_user']  = $temp_order_user;
        }

        $res['total_sub_count'] = Subscribe::query()
            ->where('relation_id', '=', $live_id)
            ->where('type', '=', 3)
            ->where('status', '=', 1)
            ->count();

        //为购买人数
        $res['total_not_buy'] = $res['total_sub_count'] - $res['total_order_user'];
        $res['total_not_buy'] = $res['total_not_buy'] < 0 ? 0 : $res['total_not_buy'];

        //观看时常大于30分钟的
//            $more_than_30_min_sql = "SELECT count(user_id) as user_count from (
//SELECT user_id,count(*) counts from nlsg_live_online_user where live_id = $live_id GROUP BY user_id
//) as a where counts >= 30";
//            $temp_res['more_than_30m'] = DB::select($more_than_30_min_sql)[0]->user_count;

        //观看时常大于60分钟的
//            $more_than_60_min_sql = "SELECT count(user_id) as user_count from (
//SELECT user_id,count(*) counts from nlsg_live_online_user where live_id = $live_id GROUP BY user_id
//) as a where counts >= 60";
//            $temp_res['more_than_60m'] = DB::select($more_than_60_min_sql)[0]->user_count;

        $res['more_than_30m'] = $res['more_than_60m'] = 0;

        $res['live_status'] = 1;  //默认值
        $channel            = LiveInfo::where('live_pid', $live_id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();

        if ($channel) {
            if ($channel->is_begin === 0 && $channel->is_finish === 0) {
                $res['live_status'] = 1;
            } elseif ($channel->is_begin === 1 && $channel->is_finish === 0) {
                $res['live_status'] = 3;
            } elseif ($channel->is_begin === 0 && $channel->is_finish === 1) {
                $res['live_status'] = 2;
            }
        }


        return $res;

    }

    //获得名下所有渠道的user_id
    public function twitterIdList($phone = '', $user_id = '')
    {
        return BackendLiveRole::where(function ($query) use ($phone, $user_id) {
            $query->where('parent', '=', $phone)
                ->orWhere('parent_id', '=', $user_id);
        })->pluck('son_id')->toArray();
    }

    public function flagPosterList($params, $user)
    {
        $live_id = $params['live_id'] ?? 0;
        $page    = $params['page'] ?? 1;
        $size    = $params['size'] ?? 10;
        $status  = $params['status'] ?? 0;

        if (empty($live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $model = new LiveSonFlagPoster();

        $top_user_id = 0;
        if ($user['role_id'] === 1 || ($user['user_id'] === 169209)) {
            $top_user_id = 169209;
        }

        $model->createPosterByLiveId($live_id, $top_user_id);

        return $model->getList([
            'live_id' => $live_id, 'page' => $page, 'size' => $size,
            'status'  => $status, 'top_user_id' => $top_user_id
        ]);
    }

    public function flagPosterStatus($params)
    {
        $id   = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check_id = LiveSonFlagPoster::where('id', '=', $id)->first();

        switch ($flag) {
            case 'on':
                $check_id->status = 2;
                break;
            case 'off':
                $check_id->status = 1;
                $check_id->live_son_flag_brush_status = 0;
                break;
            case 'del':
                $check_id->is_del = 1;
                $check_id->live_son_flag_brush_status = 0;
                break;
            case 'brush_on':
                if ($check_id->status !== 2){
                    return ['code'=>false,'msg'=>'直播未开启'];
                }
                $check_id->live_son_flag_brush_status = 1;
                break;
            case 'brush_off':
                $check_id->live_son_flag_brush_status = 0;
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = $check_id->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        }

        return ['code' => true, 'msg' => '成功'];

    }

}
