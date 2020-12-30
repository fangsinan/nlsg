<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class VipUser extends Base
{
    protected $table = 'nlsg_vip_user';

    protected $fillable = ['id', 'user_id', 'nickname', 'username', 'password', 'level', 'inviter', 'inviter_vip_id', 'source', 'source_vip_id',
        'start_time', 'expire_time', 'status', 'is_default', 'order_id', 'area', 'is_open_360', 'time_begin_360', 'time_end_360',];

    public static function newVipInfo($user_id)
    {
        $now_date = date('Y-m-d H:i:s');
        $check = self::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('start_time', '<', $now_date)
            ->where('expire_time', '>', $now_date)
            ->select(['id as vip_id', 'level', 'start_time', 'expire_time', 'is_open_360',
                'time_begin_360', 'time_end_360'])
            ->first();
        if (empty($check)) {
            return ['vip_id' => 0, 'level' => 0, 'start_time' => '', 'expire_time' => '', 'is_open_360' => 0];
        } else {
            return $check->toArray();
        }
    }

    public function homePage($user, $params)
    {
        //卡片(昵称,是否开通,到期天数,价格)
        $card_data['nickname'] = $user['nickname'] ?? '';
        $card_data['headimg'] = $user['headimg'] ?? '/image/202009/13f952e04c720a550193e5655534be86.jpg';
        $card_data['level'] = 0;
        $card_data['expire_time'] = '';
        $card_data['surplus_days'] = 0;
        $card_data['price'] = ConfigModel::getData(25);
        $card_data['is_login'] = empty($user) ? 0 : 1;

        if (empty($user['new_vip']['level'] ?? 0)) {
            $card_data['is_open'] = 0;
        } else {
            $card_data['level'] = $user['new_vip']['level'];

            if ($user['new_vip']['level'] == 1) {
                $card_data['is_open'] = 1;
                $card_data['surplus_days'] = intval((strtotime($user['new_vip']['expire_time']) - time()) / 86400);
                $card_data['expire_time'] = $user['new_vip']['expire_time'];
            } else {
                if ($user['new_vip']['is_open_360'] == 1) {
                    $card_data['is_open'] = 1;
                    $card_data['surplus_days'] = intval((strtotime($user['new_vip']['time_end_360']) - time()) / 86400);
                    $card_data['expire_time'] = $user['new_vip']['time_end_360'];
                } else {
                    $card_data['is_open'] = 0;
                }
            }
        }

        //师资阵容
        $author_order_str = ConfigModel::getData(30);
        $author_order_str = 'FIELD(id,' . $author_order_str . ') desc';
        $author = User::where('is_author', '=', 1)
            ->where('status', '=', 1)
            ->where('show_in_vip_page', '=', 1)
            ->select(['id', 'nickname', 'image_for_vip_page as headimg', 'intro_for_360'])
            ->orderByRaw($author_order_str)
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->toArray();

        if (0) {
            //精品课
            $works_id_list = ConfigModel::getData(27);
            $works_order_str = 'FIELD(w.id,' . $works_id_list . ') asc';
            $works_list = DB::table('nlsg_works as w')
                ->leftJoin('nlsg_column as c', 'w.column_id', '=', 'c.id')
                ->whereIn('w.id', explode(',', $works_id_list))
                ->where('w.status', '=', 4)
                ->orderByRaw($works_order_str)
                ->select(['w.id', 'w.type as works_type', 'w.title', 'w.subtitle',
                    'w.cover_img', 'w.image_for_vip_page as detail_img', 'w.price', 'c.type', 'c.column_type'])
                ->limit(6)
                ->get()
                ->toArray();
        } else {
            //精品课
            $vwlModel = new VipWorksList();
            $works_list = $vwlModel->getList(1, 0, 6);
        }


        //长图
        $detail_image = ConfigModel::getData(28);

        $res['card_data'] = $card_data;
        $res['author'] = ['cover_img' => ConfigModel::getData(31), 'list' => $author];
        $res['works_list'] = ['cover_img' => ConfigModel::getData(32), 'list' => $works_list];
        $res['detail_image'] = $detail_image;
        return $res;
    }

    public static function IsNewVip($uid)
    {
        if (!$uid) return false;
        $UserInfo = VipUser::select('level', 'expire_time')->where(['user_id' => $uid, 'is_default' => 1, 'status' => 1])->first();
        //$UserInfo = VipUser::where()->get();

        //return $model->getLastQuery();
        $time = date('Y-m-d', (time() + 86400));
        //判断会员
        if (!empty($UserInfo) && in_array($UserInfo->level, [1, 2,]) && $UserInfo->expire_time > $time) { //会员
            return $UserInfo->level;
        } else {
            return 0;
        }
    }

    public function orderHistory()
    {
        return $this->hasOne(Order::class, 'user_id', 'user_id')
            ->where('type', '=', 16)
            ->where('status', '=', 1)
            ->select(['id', 'user_id', 'created_at'])
            ->orderBy('id', 'desc');
    }

    public function codeHistory()
    {
        return $this->hasOne(VipRedeemUser::class, 'user_id', 'user_id')
            ->where('status', '=', 2)
            ->select(['id', 'user_id', 'updated_at'])
            ->orderBy('updated_at', 'desc');
    }

    public function nowLevel()
    {
        return $this->hasOne(VipUser::class, 'user_id', 'user_id')
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->whereRaw('expire_time > NOW()')
            ->select(['id', 'user_id', 'level', 'expire_time']);
    }

    public function assignCount()
    {
        return $this->hasOne(VipRedeemAssign::class, 'receive_vip_id', 'id')
            ->where('status', '=', 1)
            ->select('receive_vip_id', DB::raw('sum(num) as count'));
    }

    public function assignHistory()
    {
        return $this->hasMany(VipRedeemAssign::class, 'receive_vip_id', 'id');
    }

    public function openHistory($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        $sql = 'select * from (
SELECT a.id,1 as type,a.live_id as flag_id,a.pay_time,u.id as inviter_id,u.phone as inviter_phone,v.level as inviter_level,a.ordernum
from nlsg_order as a
left join nlsg_user as u on a.twitter_id = u.id
left join nlsg_vip_user as v on u.id = v.user_id and v.status = 1 and v.is_default = 1 and v.expire_time > now()
where a.user_id = ' . $user_id . ' and  a.type = 16 and a.status = 1

UNION ALL

SELECT a.id,2 as type,a.redeem_code_id as flag_id,a.updated_at as pay_time,
u.id as inviter_id,u.phone as inviter_phone,v.level as inviter_level ,\'\' as ordernum
from nlsg_vip_redeem_user as a
left join nlsg_vip_user as v on v.id = a.vip_id
left join nlsg_user as u on v.user_id = u.id
where a.user_id = ' . $user_id . ' and a.status = 2
) as z ORDER BY pay_time asc,id ASC';

        return DB::select($sql);

    }

}
