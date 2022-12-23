<?php


namespace App\Servers;


use App\Models\PayRecordDetail;
use App\Models\User;
use App\Models\VipRedeemAssign;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use App\Models\VipUserBind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VipServers
{
    public function list($params, $role_id)
    {
        $size  = $params['size'] ?? 10;
        $query = VipUser::query()
            ->where('status', '=', '1');
        if ($role_id == 28) {
            $query = $query->where('created_at', '>', '2022-01-01');
        }
        $query = $query->orderBy('created_at', 'asc')
            ->groupBy('user_id')
            ->select(['id', 'id as vip_id', 'user_id', 'nickname', 'username']);

        $query->with(['nowLevel']);

        if (!empty($params['id'] ?? 0) || !empty($params['user_id'] ?? 0)) {
            $query->with(['nowLevel.assignCount', 'nowLevel.assignHistory']);
        }

        if (!empty($params['id'] ?? '')) {
            $query->where('id', '=', $params['id']);
        }

        if (!empty($params['user_id'] ?? '')) {
            $query->where('user_id', '=', $params['user_id']);
        }

        if (!empty($params['username'] ?? '')) {
            $query->where('username', 'like', '%' . trim($params['username']) . '%');
        }

        switch (intval($params['level'] ?? '')) {
            case 1:
                $query->whereHas('nowLevel', function (Builder $q) {
                    $q->where('level', '=', 1);
                });
                break;
            case 2:
                $query->whereHas('nowLevel', function (Builder $q) {
                    $q->where('level', '=', 2);
                });
                break;
        }

        $list = $query->paginate($size);

        foreach ($list as $v) {
            if (!empty($params['id'] ?? 0) || !empty($params['user_id'] ?? 0)) {
                $vModel          = new VipUser();
                $v->open_history = $vModel->openHistory($v->user_id);


                //360的具体信息
                $v->info_360 = $vModel->vip360Info($v->id, $v->user_id);

            }
        }

        return $list;
    }

    public function change360ExpireTime($params, $admin_id)
    {
        $vip_id      = $params['vip_id'] ?? 0;
        $user_id     = $params['user_id'] ?? 0;
        $expire_time = $params['expire_time'] ?? '';

        if (empty($vip_id) || empty($user_id) || empty($expire_time)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $expire_time = date('Y-m-d 23:59:59', strtotime($expire_time));

        $check = VipUser::query()
            ->where('id', '=', $vip_id)
            ->where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->select(['id', 'user_id', 'level', 'start_time', 'expire_time', 'status', 'is_default', 'is_open_360',
                      'time_begin_360', 'time_end_360', 'username'])
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '参数错误,会员信息不存在'];
        }

        $success_msg = [];

        //修改
        if ($check->level === 1) {

            $success_msg[] = '修改到期时间:' . $check->expire_time . '更改为:' . $expire_time;

            $check->expire_time = $expire_time;

        } else {
            if ($check->is_open_360 === 1) {

                $success_msg[] = '修改到期时间:' . $check->time_end_360 . '更改为:' . $expire_time;

                $check->time_end_360 = $expire_time;

            } else {
                return ['code' => false, 'msg' => '当前用户没有开通过360'];
            }
        }

        $log_data             = [];
        $log_data['admin_id'] = $admin_id;
        $log_data['phone']    = $check->username;
        $log_data['user_id']  = $check->user_id;
        $log_data['vip_id']   = $check->id;
        $log_data['record']   = implode(';', $success_msg);

        DB::beginTransaction();

        $expire_res = $check->save();
        if (!$expire_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '修改失败,请重试.'];
        }

        $log_res = DB::table('nlsg_vip_admin_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '日志写入失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功', 'success_msg' => $success_msg];

    }

    public function assign($params, $admin_id = 0)
    {
        if (empty($admin_id)) {
            return ['code' => false, 'msg' => '用户错误'];
        }

        $vip_id  = $params['vip_id'] ?? 0;
        $user_id = $params['user_id'] ?? 0;

        $check_vip = VipUser::where('id', '=', $vip_id)
            ->where('user_id', '=', $user_id)
            ->where('level', '=', 2)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->first();
        if (empty($check_vip)) {
            return ['code' => false, 'msg' => '用户身份错误'];
        }

        if (empty($user_id) || empty($vip_id) || !isset($params['num']) || !isset($params['status'])) {
            return ['code' => false, 'msg' => '参数错误' . __LINE__];
        }
        if (!in_array($params['status'], [1, 2])) {
            return ['code' => false, 'msg' => '状态错误' . __LINE__];
        }
        if (!is_numeric($params['num'])) {
            return ['code' => false, 'msg' => '数量错误'];
        }

        $success_msg = [];

        DB::beginTransaction();

        switch ($params['flag'] ?? '') {
            case 'add':
                $data['type']           = 1;
                $data['admin_uid']      = $admin_id;
                $data['receive_uid']    = $user_id;
                $data['receive_vip_id'] = $vip_id;
                $data['num']            = $params['num'];
                $data['status']         = $params['status'];
                $res                    = DB::table('nlsg_vip_redeem_assign')->insert($data);
                $success_msg[]          = '添加配额' . $params['num'];
                break;
            case 'edit':
                $id    = $params['assign_history_id'] ?? 0;
                $check = VipRedeemAssign::where('id', '=', $id)
                    ->where('receive_vip_id', '=', $vip_id)
                    ->first();
                if (empty($check)) {
                    return ['code' => false, 'msg' => '参数错误'];
                }
                $success_msg[] = '修改配额' . $check->num . '->' . $params['num'];
                $update        = [];
                $update['num'] = $params['num'];
                $res           = DB::table('nlsg_vip_redeem_assign')
                    ->where('id', '=', $check->id)
                    ->update($update);
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $log_data             = [];
        $log_data['admin_id'] = $admin_id;
        $log_data['phone']    = $check_vip->username;
        $log_data['user_id']  = $check_vip->user_id;
        $log_data['vip_id']   = $check_vip->id;
        $log_data['record']   = implode(';', $success_msg);
        $log_res              = DB::table('nlsg_vip_admin_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '日志写入失败'];
        }

        if ($res) {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

    }

    public function openVip($user_id, $phone, $channel = '')
    {
        $now_date  = date('Y-m-d H:i:s');
        $user_info = User::where('id', '=', $user_id)->first();
        $check_vip = VipUser::where('user_id', '=', $user_id)
            ->where('username', $phone)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->first();
        if ($check_vip) {
            if ($check_vip->level == 1) {
                //是360,续期
                if ($check_vip->expire_time > $now_date) {
                    $check_vip->expire_time = date('Y-m-d 23:59:59', strtotime($check_vip->expire_time . '+1 years'));
                } else {
                    $check_vip->expire_time = date('Y-m-d 23:59:59', strtotime('+1 years'));
                }
            } else {
                //是钻石
                if ($check_vip->is_open_360 == 1) {
                    if ($check_vip->time_end_360 > $now_date) {
                        $check_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime($check_vip->time_end_360 . '+1 years'));
                    } else {
                        $check_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime('+1 years'));
                    }
                } else {
                    $check_vip->is_open_360    = 1;
                    $check_vip->time_begin_360 = $now_date;
                    $check_vip->time_end_360   = date('Y-m-d 23:59:59', strtotime('+1 years'));
                }
            }
        } else {
            $check_vip              = new VipUser();
            $check_vip->user_id     = $user_id;
            $check_vip->nickname    = $user_info->nickname;
            $check_vip->username    = $phone;
            $check_vip->level       = 1;
            $check_vip->start_time  = $now_date;
            $check_vip->expire_time = date('Y-m-d 23:59:59', strtotime('+1 years'));
            $check_vip->status      = 1;
            $check_vip->is_default  = 1;
            $check_vip->channel     = $channel;
        }
        $check_vip->save();
        VipRedeemUser::subWorksOrGetRedeemCode($user_id);

    }

    public function createVip_2($params, $admin_id)
    {
        $phone    = $params['phone'] ?? 0;
        $now_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d 23:59:59', strtotime("+2 years"));

        $check_vip = VipUser::where('username', '=', $phone)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('expire_time', '>=', $now_date)
            ->first();

        $check_user = User::where('phone', '=', $phone)->first();
        if (empty($check_user)) {
            return ['code' => false, 'msg' => '该用户没有注册'];
        }

        $success_msg = [];

        if (empty($check_vip)) {
            //开通
            $this_vip_data                = [];
            $this_vip_data['user_id']     = $check_user->id;
            $this_vip_data['nickname']    = substr_replace($phone, '****', 3, 4);
            $this_vip_data['username']    = $phone;
            $this_vip_data['level']       = 2;
            $this_vip_data['is_default']  = 1;
            $this_vip_data['start_time']  = $now_date;
            $this_vip_data['channel']     = 'backend_open';
            $this_vip_data['expire_time'] = $end_date;
            $success_msg[]                = $phone . '开通钻石.有效期' . $now_date . '至' . $this_vip_data['expire_time'];
            $this_vip_res                 = DB::table('nlsg_vip_user')->insertGetId($this_vip_data);
            if ($this_vip_res) {
                $check_vip = VipUser::where('id', '=', $this_vip_res)->first();
            }

        } else {
            if ($check_vip->level == 1) {
                //升级
                $this_old_vip             = VipUser::whereId($check_vip->id)->first();
                $this_old_vip->status     = 0;
                $this_old_vip->is_default = 0;
                $this_vip_360_res         = $this_old_vip->save();
                if (!$this_vip_360_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '修改原vip信息错误'];
                }

                $this_vip_data                   = [];
                $this_vip_data['user_id']        = $check_user->id;
                $this_vip_data['nickname']       = substr_replace($phone, '****', 3, 4);
                $this_vip_data['username']       = $phone;
                $this_vip_data['level']          = 2;
                $this_vip_data['is_default']     = 1;
                $this_vip_data['start_time']     = $now_date;
                $this_vip_data['channel']        = 'backend_open';
                $this_vip_data['expire_time']    = $end_date;
                $this_vip_data['is_open_360']    = 1;
                $this_vip_data['time_begin_360'] = $this_old_vip->start_time;
                $this_vip_data['time_end_360']   = $this_old_vip->expire_time;

                $success_msg[] = $phone . '开通钻石.有效期' . $now_date . '至' . $this_vip_data['expire_time'];
                $success_msg[] = $phone . '钻石同时订阅360,有效期:' . $this_vip_data['time_begin_360'] . '至' . $this_vip_data['time_end_360'];
                $this_vip_res  = DB::table('nlsg_vip_user')->insertGetId($this_vip_data);
                if ($this_vip_res) {
                    $check_vip = VipUser::where('id', '=', $this_vip_res)->first();
                }

            } else {
                //续费
                $this_vip        = VipUser::whereId($check_vip->id)->first();
                $old_expire_time = $this_vip->expire_time;
                if ($this_vip->expire_time > $now_date) {
                    $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime($this_vip->expire_time . "+2 years"));
                } else {
                    $this_vip->expire_time = $end_date;
                }
                if ($this_vip->expire_time > '2038-01-01 00:00:00') {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '已达开通上限'];
                }
                $success_msg[] = $phone . '续费钻石.有效期由' . $old_expire_time . '延长至' . $this_vip->expire_time;
                $this_vip_res  = $this_vip->save();
            }
        }

        if (!$this_vip_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '写入会员信息失败'];
        }

        $log_data             = [];
        $log_data['admin_id'] = $admin_id;
        $log_data['phone']    = $phone;
        $log_data['user_id']  = $check_user->id;
        $log_data['vip_id']   = $check_vip->id;
        $log_data['record']   = implode(';', $success_msg);

        $log_res = DB::table('nlsg_vip_admin_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '日志写入失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功', 'success_msg' => $success_msg];
    }

    public function createVip_1($params, $admin_id)
    {
        $parent     = $params['parent'] ?? 0;
        $son        = $params['phone'] ?? 0;
        $send_money = $params['send_money'] ?? 0;
        $now        = time();
        $now_date   = date('Y-m-d H:i:s');
        $end_date   = date('Y-m-d 23:59:59', strtotime("+1 years"));

        //新开通会员
        $inviter_level    = 0;
        $inviter          = 0;
        $inviter_vip_id   = 0;
        $inviter_username = '';
        $source           = 0;
        $source_vip_id    = 0;
        $check_parent     = [];
        if (!empty($parent)) {
            $check_parent = VipUser::where('username', '=', $parent)
                ->where('status', '=', 1)
                ->where('is_default', '=', 1)
                ->where('expire_time', '>=', $now_date)
                ->first();
            if (empty($check_parent)) {
                return ['code' => false, 'msg' => $parent . ':不是vip,无法绑定'];
            } else {
                $inviter          = $check_parent->user_id;
                $inviter_vip_id   = $check_parent->id;
                $inviter_level    = $check_parent->level;
                $inviter_username = $check_parent->username;
                if ($inviter_level == 1) {
                    $source        = $check_parent->source;
                    $source_vip_id = $check_parent->source_vip_id;
                } else {
                    $source        = $check_parent->user_id;
                    $source_vip_id = $check_parent->id;
                }
            }

        }

        if (empty($son)) {
            return ['code' => false, 'msg' => '开通人不能为空'];
        }
        $check_phone = $user = User::firstOrCreate([
            'phone' => $son,
        ], [
            'nickname' => substr_replace($son, '****', 3, 4),
        ]);

        if (empty($check_phone)) {
            return ['code' => false, 'msg' => $son . '没注册'];
        }

        $check_son = VipUser::where('username', '=', $son)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('expire_time', '>=', $now_date)
            ->first();

        $bind_info = DB::table('nlsg_vip_user_bind as ub')
            ->join('nlsg_vip_user as vu', 'ub.parent', '=', 'vu.username')
            ->where('ub.son', '=', $son)
            ->whereRaw('(ub.life = 1 or (ub.life = 2 and ub.begin_at < now() and ub.end_at > now()))')
            ->where('vu.status', '=', 1)
            ->where('vu.is_default', '=', 1)
            ->where('vu.expire_time', '>', $now_date)
            ->select(['vu.id', 'vu.user_id', 'vu.username', 'vu.level',
                      'vu.inviter', 'vu.inviter_vip_id',
                      'source', 'source_vip_id'])
            ->first();

        if (!empty($bind_info)) {
            $inviter          = $bind_info->user_id;
            $inviter_vip_id   = $bind_info->id;
            $inviter_level    = $bind_info->level;
            $inviter_username = $bind_info->username;
            if ($inviter_level == 1) {
                $source        = $bind_info->source;
                $source_vip_id = $bind_info->source_vip_id;
            } else {
                $source        = $bind_info->user_id;
                $source_vip_id = $bind_info->id;
            }
        }

        if (!empty($check_parent) && !empty($bind_info) && $check_parent->username !== $bind_info->username) {
            return ['code' => false, 'msg' => '开通人已被关系保护:' . $bind_info->username];
        }

        $success_msg  = [];
        $this_vip_res = false;

        DB::beginTransaction();

        switch (intval($check_son->level ?? 0)) {
            case 0:
                $this_vip_data                   = [];
                $this_vip_data['user_id']        = $check_phone->id;
                $this_vip_data['nickname']       = substr_replace($son, '****', 3, 4);
                $this_vip_data['username']       = $son;
                $this_vip_data['level']          = 1;
                $this_vip_data['inviter']        = $inviter;
                $this_vip_data['inviter_vip_id'] = $inviter_vip_id;
                $this_vip_data['source']         = $source;
                $this_vip_data['source_vip_id']  = $source_vip_id;
                $this_vip_data['is_default']     = 1;
                $this_vip_data['start_time']     = $now_date;
                $this_vip_data['channel']        = 'backend_open';
                $this_vip_data['expire_time']    = $end_date;

                $success_msg[] = $son . '开通360.有效期' . $now_date . '至' . $this_vip_data['expire_time'];
                if (!empty($inviter_username)) {
                    $success_msg[] = '上级:' . $inviter_username;
                }

                $this_vip_res = DB::table('nlsg_vip_user')->insertGetId($this_vip_data);
                if ($this_vip_res) {
                    $check_son = VipUser::where('id', '=', $this_vip_res)->first();
                }
                break;
            case 1:
                $this_vip        = VipUser::whereId($check_son->id)->first();
                $old_expire_time = $this_vip->expire_time;
                if ($this_vip->expire_time > $now_date) {
                    $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime($this_vip->expire_time . "+1 years"));
                } else {
                    $this_vip->expire_time = $end_date;
                }

                if ($this_vip->expire_time > '2038-01-01 00:00:00') {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '已达开通上限'];
                }

                $success_msg[] = $son . '续费360.有效期由' . $old_expire_time . '延长至' . $this_vip->expire_time;

                $this_vip_res = $this_vip->save();

                if (empty($check_son->inviter_vip_id ?? 0)) {
                    //之前没有推荐人,需要添加推荐人为现在的推荐人
                    $this_vip                 = VipUser::whereId($check_son->id)->first();
                    $this_vip->inviter        = $inviter;
                    $this_vip->inviter_vip_id = $inviter_vip_id;
                    $this_vip->source         = $source;
                    $this_vip->source_vip_id  = $source_vip_id;
                    $this_vip->save();
                    if (!empty($inviter_username)) {
                        $success_msg[] = $son . '的推荐人由空修改为' . $inviter_username;
                    }
                } else {
                    //之前有收益,就给老上家
                    $temp_inviter_info = VipUser::where('user_id', '=', $check_son->inviter)
                        ->where('status', '=', 1)
                        ->where('is_default', '=', 1)
                        ->where('expire_time', '>=', $now_date)
                        ->first();
                    if (empty($temp_inviter_info)) {
                        $inviter = $inviter_vip_id = $inviter_level = 0;
                    } else {
                        $inviter          = $temp_inviter_info->user_id;
                        $inviter_vip_id   = $temp_inviter_info->id;
                        $inviter_level    = $temp_inviter_info->level;
                        $inviter_username = $temp_inviter_info->username;
                        $success_msg[]    = $son . '的老上级是:' . $inviter_username;
                    }
                }
                break;
            case 2:
                $this_vip              = VipUser::whereId($check_son->id)->first();
                $success_msg[]         = $son . '已经是钻石了';
                $this_vip->is_open_360 = 1;
                $temp_msg              = $son;
                if (empty($this_vip->time_begin_360)) {
                    $this_vip->time_begin_360 = $now_date;
                    $temp_msg                 .= '开通360.有效期由' . $now_date;
                } else {
                    $temp_msg .= '续费360.有效期由' . $this_vip->time_begin_360;
                }
                if (empty($this_vip->time_end_360)) {
                    $this_vip->time_end_360 = $end_date;
                } else {
                    $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime($this_vip->time_end_360 . "+1 years"));
                }
                if ($this_vip->expire_time > '2038-01-01 00:00:00') {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '已达开通上限'];
                }
                $temp_msg      .= '至' . $this_vip->time_end_360;
                $success_msg[] = $temp_msg;
                $this_vip_res  = $this_vip->save();

                $inviter        = $check_son->user_id;
                $inviter_vip_id = $check_son->id;
                $inviter_level  = 2;
                break;
        }
        if (!$this_vip_res) {
            return ['code' => false, 'msg' => '失败,请重试.' . __LINE__];
        }

        VipRedeemUser::subWorksOrGetRedeemCode($check_phone->id);

        if ($send_money && !empty($inviter)) {
            //添加虚拟订单
            $add_order_data['type']           = 16;
            $add_order_data['user_id']        = $check_phone->id;
            $add_order_data['status']         = 1;
            $add_order_data['pay_time']       = $now_date;
            $add_order_data['created_at']     = $now_date;
            $add_order_data['price']          = 360;
            $add_order_data['pay_price']      = 360;
            $add_order_data['start_time']     = $now_date;
            $add_order_data['end_time']       = $end_date;
            $add_order_data['twitter_id']     = $inviter;
            $add_order_data['vip_order_type'] = 1;
            $add_order_data['remark']         = 'uc_remove';
            $add_order_data['ordernum']       = date('YmdHis') . rand(1000, 9999);
            $add_order_data['activity_tag']   = 'backend_open';
            $add_order_res                    = DB::table('nlsg_order')->insertGetId($add_order_data);
            if ($add_order_res) {
                $success_msg[] = '成功创建虚拟vip订单:' . $add_order_data['ordernum'];
            } else {
                DB::rollBack();
                return ['code' => false, 'msg' => '创建虚拟订单失败'];
            }

            //收益动作
            if (!empty($inviter) && !empty($inviter_level)) {
                $pdModel              = new PayRecordDetail();
                $pdModel->type        = 11;
                $pdModel->ordernum    = $add_order_data['ordernum'];
                $pdModel->ctime       = $now;
                $pdModel->user_id     = $inviter;
                $pdModel->user_vip_id = $inviter_vip_id;
                if ($inviter_level == 1) {
                    $pdModel->price = 108;
                } else {
                    $pdModel->price = 180;
                }
                $pdModel->vip_id = $check_son->id;
                $pd_res          = $pdModel->save();
                if ($pd_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '添加收益失败'];
                } else {
                    $success_msg[] = '添加收益成功:' . $inviter . '获得' . $pdModel->price;
                }
            } else {
                $success_msg[] = '没有有效受益人,无法添加收益';
            }
        } else {
            $success_msg[] = '没有收益划分动作';
        }

        $log_data             = [];
        $log_data['admin_id'] = $admin_id;
        $log_data['phone']    = $son;
        $log_data['user_id']  = $check_son->user_id;
        $log_data['vip_id']   = $check_son->id;
        $log_data['record']   = implode(';', $success_msg);

        $log_res = DB::table('nlsg_vip_admin_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '日志写入失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功', 'success_msg' => $success_msg];
    }


    public function createVip($params, $admin_id = 0)
    {
        $flag = $params['flag'] ?? 0;

        switch (intval($flag)) {
            case 1:
                return $this->createVip_1($params, $admin_id);
            case 2:
                return $this->createVip_2($params, $admin_id);
            case 3:
                //临时360权限
                return $this->createVip_3($params, $admin_id);
            default:
                return ['code' => false, 'msg' => '开通类型错误'];
        }

    }

    public function vipUserBindClear()
    {
        VipUserBind::query()
            ->where('status', '=', 1)
            ->where('end_at', '<=', date('Y-m-d H:i:s'))
            ->update([
                'status' => 2
            ]);
    }

    public function createVip_3($params, $admin_id)
    {
        $phone = $params['phone'] ?? 0;
        if (empty($phone)) {
            return ['code' => false, 'msg' => '开通人不能为空'];
        }

        $now_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d 23:59:59', strtotime("+1 months"));

        $check_phone = User::query()
            ->firstOrCreate([
                'phone' => $phone,
            ], [
                'nickname' => substr_replace($phone, '****', 3, 4),
            ]);

        if (!$check_phone) {
            return ['code' => false, 'msg' => $phone . '没注册'];
        }

        $check_son = VipUser::query()
            ->where('username', '=', $phone)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('expire_time', '>=', $now_date)
            ->first();

        if (!empty($check_son)) {
            return ['code' => false, 'msg' => '已经是会员,无法继续开通体验会员.'];
        }

        DB::beginTransaction();

        $this_vip_data                   = [];
        $this_vip_data['user_id']        = $check_phone->id;
        $this_vip_data['nickname']       = substr_replace($phone, '****', 3, 4);
        $this_vip_data['username']       = $phone;
        $this_vip_data['level']          = 1;
        $this_vip_data['inviter']        = 0;
        $this_vip_data['inviter_vip_id'] = 0;
        $this_vip_data['source']         = 0;
        $this_vip_data['source_vip_id']  = 0;
        $this_vip_data['is_default']     = 1;
        $this_vip_data['start_time']     = $now_date;
        $this_vip_data['channel']        = 'backend_open';
        $this_vip_data['expire_time']    = $end_date;

        $success_msg[] = $phone . '开通360体验会员.有效期' . $now_date . '至' . $this_vip_data['expire_time'];
        if (!empty($inviter_username)) {
            $success_msg[] = '上级:' . $inviter_username;
        }

        $this_vip_res = DB::table('nlsg_vip_user')->insertGetId($this_vip_data);
        if ($this_vip_res) {
            $check_son = VipUser::query()->where('id', '=', $this_vip_res)->first();
        }

        if (!$this_vip_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试.' . __LINE__];
        }

        VipRedeemUser::subWorksOrGetRedeemCode($check_phone->id, 'backend_open_temp_360');

        $log_data             = [];
        $log_data['admin_id'] = $admin_id;
        $log_data['phone']    = $phone;
        $log_data['user_id']  = $check_son->user_id;
        $log_data['vip_id']   = $check_son->id;
        $log_data['record']   = implode(';', $success_msg);

        $log_res = DB::table('nlsg_vip_admin_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '日志写入失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功', 'success_msg' => $success_msg];

    }

    public function vipUserBindSource()
    {
        //废弃
        //处理bind的source部分
        VipUserBind::query()
            ->where('source', '=', '')
            ->where('status', '=', 2)
            ->update([
                'source' => 'NULL',
            ]);

        $list = VipUserBind::query()
            ->where('source', '=', '')
            ->where('status', '=', 1)
            ->groupBy('parent')
            ->limit(100)
            ->pluck('parent')
            ->toArray();

        if (empty($list)) {
            return true;
        }

        $check = VipUser::query()
            ->whereIn('username', $list)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->select(['username', 'source', 'source_vip_id', 'level'])
            ->with(['sourceVipInfo:id,username'])
            ->get()
            ->toArray();

        foreach ($list as $k => $v) {
            foreach ($check as $kk => $vv) {
                if ($v === $vv['username']) {
                    if ($vv['level'] === 2) {

                        //钻石合伙人,source就是自己
                        VipUserBind::query()
                            ->where('source', '=', '')
                            ->where('parent', '=', $v)
                            ->where('status', '=', 1)
                            ->update([
                                'source' => $vv['username'],
                            ]);
                    } else {
                        if (empty($vv['source_vip_info']['username'] ?? '')) {

                            VipUserBind::query()
                                ->where('source', '=', '')
                                ->where('parent', '=', $v)
                                ->where('status', '=', 1)
                                ->update([
                                    'source' => 'NULL',
                                ]);
                        } else {

                            VipUserBind::query()
                                ->where('source', '=', '')
                                ->where('parent', '=', $v)
                                ->where('status', '=', 1)
                                ->update([
                                    'source' => $vv['source_vip_info']['username'],
                                ]);
                        }
                    }
                    unset($list[$k], $check[$kk]);
                }
            }
        }

        if ($list) {
            VipUserBind::query()
                ->where('source', '=', '')
                ->where('status', '=', 1)
                ->whereIn('parent', $list)
                ->update([
                    'source' => 'NULL',
                ]);
        }

    }

}
