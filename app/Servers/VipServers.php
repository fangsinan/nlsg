<?php


namespace App\Servers;


use App\Models\User;
use App\Models\VipRedeemAssign;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VipServers
{
    public function list($params, $admin_id)
    {
        $size = $params['size'] ?? 10;
        $query = VipUser::query()
            ->orderBy('created_at', 'asc')
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
                $vModel = new VipUser();
                $v->open_history = $vModel->openHistory($v->user_id);
            }
        }

        return $list;
    }

    public function assign($params, $admin_id = 0)
    {
        if (empty($admin_id)) {
            return ['code' => false, 'msg' => '用户错误'];
        }

        $vip_id = $params['vip_id'] ?? 0;
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

        switch ($params['flag'] ?? '') {
            case 'add':
                $data['type'] = 1;
                $data['admin_uid'] = $admin_id;
                $data['receive_uid'] = $user_id;
                $data['receive_vip_id'] = $vip_id;
                $data['num'] = $params['num'];
                $data['status'] = $params['status'];
                $res = DB::table('nlsg_vip_redeem_assign')->insert($data);
                break;
            case 'edit':
                $id = $params['assign_history_id'] ?? 0;
                $check = VipRedeemAssign::where('id', '=', $id)
                    ->where('receive_vip_id', '=', $vip_id)
                    ->first();
                if (empty($check)) {
                    return ['code' => false, 'msg' => '参数错误'];
                }
                $update = [];
                $update['num'] = $params['num'];
                $res = DB::table('nlsg_vip_redeem_assign')
                    ->where('id', '=', $check->id)
                    ->update($update);
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }

    }

    public function openVip($user_id, $phone)
    {
        $now_date = date('Y-m-d H:i:s');
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
                    $check_vip->is_open_360 = 1;
                    $check_vip->time_begin_360 = $now_date;
                    $check_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime('+1 years'));
                }
            }
        } else {
            $check_vip = new VipUser();
            $check_vip->user_id = $user_id;
            $check_vip->nickname = $user_info->nickname;
            $check_vip->username = $phone;
            $check_vip->level = 1;
            $check_vip->start_time = $now_date;
            $check_vip->expire_time = date('Y-m-d 23:59:59', strtotime('+1 years'));
            $check_vip->status = 1;
            $check_vip->is_default = 1;
        }
        $check_vip->save();
        VipRedeemUser::subWorksOrGetRedeemCode($user_id);

    }

}
