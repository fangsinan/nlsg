<?php


namespace App\Models;

use App\Http\Controllers\Api\V4\CreatePosterController;
use Illuminate\Support\Facades\DB;
use function EasyWeChat\Kernel\Support\str_random;

class VipRedeemUser extends Base
{
    protected $table = 'nlsg_vip_redeem_user';


    public function list($user, $params)
    {
        if (empty($user['id'] ?? 0)) {
            $res['statistics'] = new class {
            };
            $res['count'] = new class {
            };
            $res['list'] = [];
            return $res;
        }
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);
        $price = ConfigModel::getData(25);

        $query = self::query();

        if (!empty($params['id'] ?? 0)) {
            $query->whereId($params['id']);
            $query->with(['userInfo']);
        }

        $query->where('user_id', '=', $user['id']);

        if (empty($params['id'] ?? 0)) {
            //1未使用 2已使用 3赠送中 4已送出
            switch (intval($params['flag'] ?? 1)) {
                case 1:
                    $query->where('status', '=', 1);
                    break;
                case 2:
                    $query->where('status', '=', 2);
                    break;
                case 3:
                    $query->where('status', '=', 3);
                    break;
                case 4:
                    $query->where('status', '=', 4);
                    break;
                case 5:
                    $query->where(function ($query) {
                        $query->where('status', '=', 2)->orWhere('status', '=', 4);
                    });
                    break;
            }
        }


        switch ($params['ob'] ?? '') {
            case 't_asc':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        $query->orderBy('id', 'desc');
        $query->with(['codeInfo']);

        $list = $query->limit($size)
            ->offset(($page - 1) * $size)
            ->select(['id', 'redeem_code_id', 'status', 'created_at', 'user_id',
                DB::raw("concat('¥',$price) as price")])
            ->get();

        if ($list->isEmpty()) {
            $list = [];
        } else {
            if (!empty($params['id'] ?? 0)) {
                foreach ($list as $v) {
                    if ($v->status == 1 || $v->status == 3) {
                        $base_url = ConfigModel::getData(26);
                        $base_url = parse_url($base_url);

                        //todo 分享二维码参数待定
                        $url_data = [
                            'id' => $params['id'],
                            'r' => str_random(10),
                            'time' => time(),
                            'user_id' => $user['id']
                        ];
                        $url_data = http_build_query($url_data);

                        $qr_url = $base_url['scheme'] . '://' . $base_url['host'] . $base_url['path'];
                        $qr_url = $qr_url . '?' . $url_data;

                        $qrModel = new CreatePosterController();
                        $qr_data = $qrModel->createQRcode($qr_url, true, true, true);
                        $qr_data = ConfigModel::base64Upload(100, $qr_data);
                        $qr_data = $qr_data['url'] . $qr_data['name'];
                        //$qr_data = config('env.APP_URL') . '/public/image/' . $qr_data;
                        $v->qr_code = $qr_data;
                    }
                }
            }
        }

        $statistics = VipRedeemAssign::statistics($user);
        $count['flag_1'] = self::where('user_id', '=', $user['id'])->where('status', '=', 1)->count();
        $count['flag_2'] = self::where('user_id', '=', $user['id'])->where('status', '=', 2)->count();
        $count['flag_3'] = self::where('user_id', '=', $user['id'])->where('status', '=', 3)->count();
        $count['flag_4'] = self::where('user_id', '=', $user['id'])->where('status', '=', 4)->count();
        $count['flag_5'] = self::where('user_id', '=', $user['id'])->where(function ($query) {
            $query->where('status', '=', 2)->orWhere('status', '=', 4);
        })->count();

        return ['statistics' => $statistics, 'count' => $count, 'list' => $list];
    }

    public function send($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

//        if (empty($user['new_vip']['level'] ?? 0)) {
//            return ['code' => false, 'msg' => '会员信息错误'];
//        }

        $check = self::query()->whereId($params['id'])->where('user_id', '=', $user['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 1) {
            switch ($check->status) {
                case 2:
                    return ['code' => false, 'msg' => '兑换券已被使用'];
                case 3:
                    return ['code' => false, 'msg' => '兑换券状态错误'];
                case 4:
                    return ['code' => false, 'msg' => '兑换券已送出'];
            }
        }

        $check->status = 3;
        $res = $check->save();

        if ($res === false) {
            return ['code' => false, 'msg' => '失败,请重试'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    public function takeBack($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

//        if (empty($user['new_vip']['level'] ?? 0)) {
//            return ['code' => false, 'msg' => '会员信息错误'];
//        }
        $check = self::query()->whereId($params['id'])->where('user_id', '=', $user['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 3) {
            return ['code' => false, 'msg' => '兑换券已被使用'];
        }
        $check->status = 1;
        $res = $check->save();

        if ($res === false) {
            return ['code' => false, 'msg' => '失败,请重试'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    public function info($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (empty($params['user_id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::query()->whereId($params['id'])->where('user_id', '=', $params['user_id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 3) {
            return ['code' => false, 'msg' => '兑换券已被使用'];
        }
//        if ($check->user_id == $user['id']) {
//            return ['code' => false, 'msg' => '兑换券错误'];
//        }
        $price = ConfigModel::getData(25);
        $res = self::whereId($params['id'])
            ->with(['userInfo', 'codeInfo'])
            ->select(['id', 'redeem_code_id', 'user_id', DB::raw("concat('¥',$price) as price")])
            ->first();

        if (empty($res)) {
            return new class {
            };
        }
        return $res;
    }

    public function get($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::query()->whereId($params['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 3) {
            return ['code' => false, 'msg' => '兑换券状态错误'];
        }
        if ($check->user_id == $user['id']) {
            return ['code' => false, 'msg' => '兑换券错误'];
        }

        //查询当前领取用户的vip信息 如果不是钻石,需要校验当前源账户是不是兑换券所有人, 没有源账户就查询关系保护
        $check_vip = VipUser::where('user_id', '=', $user['id'])
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->first();

        if (empty($check_vip)) {
            //不是vip,查询关系保护
            $bind_user_id = VipUserBind::getBindParent($user['phone']);
            if ($bind_user_id !== 0 && intval($check->user_id) !== $bind_user_id) {
                return ['code' => false, 'msg' => '您的账号已受保护,无法领取.'];
            }
        } else {
            if ($check_vip['level'] == 1) {
                //是360,查询当前源账户是否为空
                if ($check_vip['source_vip_id'] == 0) {
                    //是0 查询关系保护
                    $bind_user_id = VipUserBind::getBindParent($user['phone']);
                    if ($bind_user_id !== 0 && intval($check->user_id) !== $bind_user_id) {
                        return ['code' => false, 'msg' => '您的账号已受保护,无法领取.'];
                    }
                } else {
                    //不是0 对比是否一直
                    if ($check->vip_id != $check_vip['source_vip_id']) {
                        return ['code' => false, 'msg' => '您的账号已受保护,无法领取.'];
                    }
                }
            }
        }

        $model = new self();
        $model->redeem_code_id = $check->redeem_code_id;
        $model->user_id = $user['id'];
        $model->parent_id = $check->user_id;
        $model->path = $check->path . ',' . $user['id'];
        $model->vip_id = $check->vip_id;
        $model->status = 1;

        DB::beginTransaction();

        $res = $model->save();
        if ($res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        $check->status = 4;
        $check_res = $check->save();
        if ($check_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function use($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::query()->whereId($params['id'])->where('user_id', '=', $user['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 1) {
            return ['code' => false, 'msg' => '兑换券状态错误'];
        }

        //不是钻石,需要校验是否有关系保护
        $bind_user_info = [];
        if ($user['new_vip']['level'] !== 2) {
            $bind_user_id = VipUserBind::getBindParent($user['phone']);
            if ($bind_user_id !== 0 && intval($check->parent_id) !== $bind_user_id) {
                return ['code' => false, 'msg' => '您的账号已受保护,无法使用.'];
            }
            if ($bind_user_id > 0) {
                $bind_user_info = VipUser::where('user_id', '=', $check->parent_id)
                    ->where('status', '=', 1)
                    ->where('is_default', '=', 1)
                    ->first();
                if (!empty($bind_user_info)) {
                    $bind_user_info = $bind_user_info->toArray();
                }
            }
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        DB::beginTransaction();

        $check->status = 2;
        $vu_res = $check->save();
        if ($vu_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        //判断用户当前级别
        switch (intval($user['new_vip']['level'])) {
            case 0:
                //不是360  开通
                $source_info = VipUser::whereId($check->vip_id)->first();
                $vip_add_data['user_id'] = $user['id'];
                $vip_add_data['nickname'] = $user['nickname'];
                $vip_add_data['username'] = $user['phone'];
                $vip_add_data['level'] = 1;
                $vip_add_data['inviter'] = $bind_user_info['user_id'] ?? 0;
                $vip_add_data['inviter_vip_id'] = $bind_user_info['id'] ?? 0;
                $vip_add_data['source'] = $source_info->user_id ?? 0;
                $vip_add_data['source_vip_id'] = $check->vip_id;
                $vip_add_data['is_default'] = 1;
                $vip_add_data['created_at'] = $now_date;
                $vip_add_data['start_time'] = $now_date;
                $vip_add_data['updated_at'] = $now_date;
                $vip_add_data['expire_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                $add_res = DB::table('nlsg_vip_user')->insert($vip_add_data);
                if (!$add_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败,请重试.'];
                }
                break;
            case 1:
                //是360 延长
                $this_vip = VipUser::whereId($user['new_vip']['vip_id'])->first();
                $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime($this_vip->expire_time . ' +1 year'));
                $update_res = $this_vip->save();
                if ($update_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败,请重试.'];
                }
                break;
            case 2:
                //钻石 修改
                $this_vip = VipUser::whereId($user['new_vip']['vip_id'])->first();
                $this_vip->is_open_360 = 1;
                if (empty($this_vip->time_begin_360)) {
                    $this_vip->time_begin_360 = $now_date;
                }
                if (empty($this_vip->time_end_360) || $this_vip->time_end_360 < $now_date) {
                    $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime('+1 year'));
                } else {
                    $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime($this_vip->time_end_360 . ' +1 year'));
                }
                $update_res = $this_vip->save();
                if ($update_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败,请重试.'];
                }
                break;
        }

        //课程与兑换卡
        $works_res = self::subWorksOrGetRedeemCode($user['id']);

        if ($works_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }


    //(加事务调用)360课程订阅和生成兑换券
    public static function subWorksOrGetRedeemCode($user_id,$activity_tag="")
    {

        $model = new VipWorksList();
        $list = $model->getList(1);

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $end_date = date('Y-m-d 23:59:59', strtotime('+1 years'));

        //2021 双十一活动
        if($activity_tag === "2021-11-1"){ //1号活动
            $end_date = date('Y-m-d 23:59:59', strtotime("+7day"));
        }else if($activity_tag === "2021-11-2"){ //2号活动
            $end_date = date('Y-m-d 23:59:59', strtotime("+1years +100day"));
        }else if($activity_tag === 'backend_open_temp_360'){
            $end_date = date('Y-m-d 23:59:59', strtotime("+1 months"));
        }

        $group_name = RedeemCode::createGroupName();

        $add_code_data = [];
        $add_sub_data = [];

        foreach ($list as $v) {
            if ($v['type'] == 1) {
                //讲座订阅
                $check = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $v['id'])
                    ->where('type', '=', 6)
                    ->where('status', '=', 1)
                    ->where('end_time', '>=', $now_date)
                    ->first();
                if (empty($check)) {
                    $add_sub_data[] = [
                        'user_id' => $user_id,
                        'type' => 6,
                        'status' => 1,
                        'relation_id' => $v['id'],
                        'pay_time' => $now_date,
                        'created_at' => $now_date,
                        'updated_at' => $now_date,
                        'give' => 14,
                        'start_time' => $now_date,
                        'end_time' => $end_date
                    ];
                } else {
                    if(!in_array($activity_tag,["2021-11-1","2021-11-2",'backend_open_temp_360'])) {

                        $add_code_data[] = [
                            'code' => $group_name . RedeemCode::get_34_Number(RedeemCode::createCodeTemp(), 5),
                            'name' => ($v['title'] ?? '讲座') . '-兑换券',
                            'new_group' => $group_name,
                            'can_use' => 1,
                            'redeem_type' => 3,
                            'goods_id' => $v['id'],
                            'user_id' => $user_id,
                            'is_new_code' => 1,
                            'created_at' => $now_date,
                            'updated_at' => $now_date
                        ];
                    }

                }

            } else {
                //作品订阅
                $check = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $v['id'])
                    ->where('type', '=', 2)
                    ->where('status', '=', 1)
                    ->where('end_time', '>=', $now_date)
                    ->first();
                if (empty($check)) {
                    $add_sub_data[] = [
                        'user_id' => $user_id,
                        'type' => 2,
                        'status' => 1,
                        'relation_id' => $v['id'],
                        'pay_time' => $now_date,
                        'created_at' => $now_date,
                        'updated_at' => $now_date,
                        'give' => 14,
                        'start_time' => $now_date,
                        'end_time' => $end_date
                    ];
                } else {
                    if(!in_array($activity_tag,["2021-11-1","2021-11-2",'backend_open_temp_360'])) {

                        $add_code_data[] = [
                            'code' => $group_name . RedeemCode::get_34_Number(RedeemCode::createCodeTemp(), 5),
                            'name' => ($v['title'] ?? '课程') . '-兑换券',
                            'new_group' => $group_name,
                            'can_use' => 1,
                            'redeem_type' => 2,
                            'goods_id' => $v['id'],
                            'user_id' => $user_id,
                            'is_new_code' => 1,
                            'created_at' => $now_date,
                            'updated_at' => $now_date
                        ];
                    }

                }
            }
        }

        if (!empty($add_code_data)) {
            $sub_res = DB::table('nlsg_redeem_code')->insert($add_code_data);
            if (!$sub_res) {
                return false;
            }
        }

        if (!empty($add_sub_data)) {
            $sub_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
            if (!$sub_res) {
                return false;
            }

        }

        return true;


    }

    public function codeInfo()
    {
        return $this->hasOne(VipRedeemCode::class, 'id', 'redeem_code_id')
            ->select(['id', 'name', 'number']);
    }

    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->select(['id', 'nickname', 'headimg']);
    }
}
