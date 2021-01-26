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

    public function jobOf1360($user_id, $order_id, $live_id)
    {
        $now_date = date('Y-m-d H:i:s');
        $user_info = User::whereId($user_id)->select(['id', 'phone'])->first();

        $user_vip_info = VipUser::where('username', '=', $user_info->phone)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->first();

        $order_info = Order::whereId($order_id)->select(['id', 'ordernum'])->first();

        $inviter_info = DB::table('nlsg_live_count_down as cd')
            ->join('nlsg_vip_user as vu', 'cd.new_vip_uid', '=', 'vu.user_id')
            ->where('cd.user_id', '=', $user_id)
            ->where('vu.status', '=', 1)
            ->where('vu.is_default', '=', 1)
            ->where('vu.expire_time', '>', $now_date)
            ->where('cd.live_id', '=', $live_id)
            ->select(['vu.id', 'vu.user_id', 'vu.username', 'vu.level',
                'vu.inviter', 'vu.inviter_vip_id',
                'source', 'source_vip_id'])
            ->first();

        $bind_info = DB::table('nlsg_vip_user_bind as ub')
            ->join('nlsg_vip_user as vu', 'ub.parent', '=', 'vu.username')
            ->where('ub.son', '=', $user_info->phone)
            ->whereRaw('(ub.life = 1 or (ub.life = 2 and ub.begin_at > now() and ub.end_at < now()))')
            ->where('vu.status', '=', 1)
            ->where('vu.is_default', '=', 1)
            ->where('vu.expire_time', '>', $now_date)
            ->select(['vu.id', 'vu.user_id', 'vu.username', 'vu.level',
                'vu.inviter', 'vu.inviter_vip_id',
                'source', 'source_vip_id'])
            ->first();

        $inviter = 0;
        $inviter_vip_id = 0;
        $inviter_level = 0;
        $source = 0;
        $source_vip_id = 0;

        if (!empty($bind_info)) {
            //优先绑定的
            $inviter = $bind_info->user_id;
            $inviter_vip_id = $bind_info->id;
            $inviter_level = $bind_info->level;
            if ($bind_info->level == 2) {
                $source = $bind_info->user_id;
                $source_vip_id = $bind_info->id;
            } else {
                $source = $bind_info->source;
                $source_vip_id = $bind_info->source_vip_id;
            }
        } else {
            //没有绑定才走推荐
            if (!empty($inviter_info)) {
                $inviter = $inviter_info->user_id;
                $inviter_vip_id = $inviter_info->id;
                $inviter_level = $inviter_info->level;
                if ($inviter_info->level == 2) {
                    $source = $inviter_info->user_id;
                    $source_vip_id = $inviter_info->id;
                } else {
                    $source = $inviter_info->source;
                    $source_vip_id = $inviter_info->source_vip_id;
                }
            }
        }

        DB::beginTransaction();

        //开通或延长
        switch (intval($user_vip_info->level ?? 0)) {
            case 0:
                $this_vip_data = [];
                $this_vip_data['user_id'] = $user_id;
                $this_vip_data['nickname'] = substr_replace($user_info->phone, '****', 3, 4);
                $this_vip_data['username'] = $user_info->phone;
                $this_vip_data['level'] = 1;
                $this_vip_data['inviter'] = $inviter;
                $this_vip_data['inviter_vip_id'] = $inviter_vip_id;
                $this_vip_data['source'] = $source;
                $this_vip_data['source_vip_id'] = $source_vip_id;
                $this_vip_data['is_default'] = 1;
                $this_vip_data['created_at'] = $now_date;
                $this_vip_data['start_time'] = $now_date;
                $this_vip_data['updated_at'] = $now_date;
                $this_vip_data['channel'] = $order_info->activity_tag;
                $this_vip_data['expire_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                $this_vip_res = DB::table('nlsg_vip_user')->insertGetId($this_vip_data);
                if ($this_vip_res) {
                    $user_vip_info = VipUser::where('username', '=', $user_info->phone)
                        ->where('status', '=', 1)
                        ->where('is_default', '=', 1)
                        ->first();
                }
                break;
            case 1:
                $this_vip = VipUser::whereId($user_vip_info->id)->first();
                if ($this_vip->expire_time > $now_date) {
                    $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime($this_vip->expire_time . ' +1 year'));
                } else {
                    $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime('+1 year'));
                }
                $this_vip_res = $this_vip->save();

                if (1) {
                    if (0) {
                        //自己续费给自己收益
                        $inviter = $user_vip_info->user_id;
                        $inviter_vip_id = $user_vip_info->id;
                        $inviter_level = 1;
                    } else {
                        if (empty($user_vip_info->inviter_vip_id ?? 0)) {
                            //之前没有推荐人,就没有收益
                            $inviter = $inviter_vip_id = $inviter_level = 0;
                        } else {
                            //之前有收益,就给老上家
                            $temp_inviter_info = VipUser::where('user_id', '=', $user_vip_info->inviter)
                                ->where('status', '=', 1)
                                ->where('is_default', '=', 1)
                                ->first();
                            if (empty($temp_inviter_info)) {
                                $inviter = $inviter_vip_id = $inviter_level = 0;
                            } else {
                                $inviter = $temp_inviter_info->user_id;
                                $inviter_vip_id = $temp_inviter_info->id;
                                $inviter_level = $temp_inviter_info->level;
                            }
                        }
                    }
                } else {
                    //自己续费没有收益
                    $inviter = $inviter_vip_id = $inviter_level = 0;
                }
                break;
            case 2:
                $this_vip = VipUser::whereId($user_vip_info->id)->first();
                $this_vip->is_open_360 = 1;
                if (empty($this_vip->time_begin_360)) {
                    $this_vip->time_begin_360 = $now_date;
                }
                if (empty($this_vip->time_end_360)) {
                    $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime('+1 year'));
                } else {
                    $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime($this_vip->time_end_360 . ' +1 year'));
                }
                $this_vip_res = $this_vip->save();
                $inviter = $user_vip_info->user_id;
                $inviter_vip_id = $user_vip_info->id;
                $inviter_level = 2;
                break;
        }

        if ($this_vip_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'error:' . __LINE__];
        }

        //收益
        if (!empty($inviter) && !empty($inviter_level)) {
            $check_pd = PayRecordDetail::where('ordernum', '=', $order_info->ordernum)
                ->where('type', '=', 11)
                ->first();
            if (empty($check_pd)) {
                $pdModel = new PayRecordDetail();
                $pdModel->type = 11;
                $pdModel->ordernum = $order_info->ordernum;
                $pdModel->ctime = time();
                $pdModel->user_id = $inviter;
                $pdModel->user_vip_id = $inviter_vip_id;
                if ($inviter_level == 1) {
                    $pdModel->price = 108;
                } else {
                    $pdModel->price = 180;
                }
                $pdModel->vip_id = $user_vip_info->id;
                $pd_res = $pdModel->save();
                if ($pd_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => 'error:' . __LINE__];
                }
            }
        }

        //开通订阅课程
        VipRedeemUser::subWorksOrGetRedeemCode($user_id);

        DB::commit();
        return ['code' => true, 'msg' => 'ok'];
    }

}
