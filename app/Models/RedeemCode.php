<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class RedeemCode extends Base
{
    protected $table = 'nlsg_redeem_code';

    public function redeemList($params, $user)
    {
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);
        $status = intval($params['status'] ?? -1);

        $query = self::where('user_id', '=', $user['id'])
            ->where('can_use', '=', 1);

        if ($status !== -1){
            $query->where('status','=',$status);
        }

        return $query->select(['id', 'code', 'name', 'status'])
            ->limit($size)
            ->orderBy('status','asc')
            ->orderBy('id','asc')
            ->offset(($page - 1) * $size)
            ->get();

    }

    //兑换
    public function redeem($params, $user)
    {
        $code = $params['code'] ?? '';
        $phone = $params['phone'] ?? '';
        $os_type = $params['os_type'] ?? 0;
        $phone = '';//关闭指定手机号兑换,只能当前账号

        if (!in_array($os_type, [1, 2, 3])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (empty($code)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_code = RedeemCode::where('code', '=', $code)
            ->first();
        if (!$check_code || $check_code->can_use <> 1) {
            return ['code' => false, 'msg' => '兑换码不存在'];
        }
        if ($check_code->status == 1) {
            return ['code' => false, 'msg' => '兑换码已使用'];
        }

        DB::beginTransaction();

        if (empty($phone)) {
            $to_user_id = $user['id'];
            $phone = $user['phone'];
        } else {
            $check_phone = User::where('phone', '=', $phone)->first();
            if ($check_phone) {
                $to_user_id = $check_phone->id;
            } else {
                $new_user = new User();
                $new_user->phone = $phone;
                $new_user->nickname = substr($phone, 0, 3) . '****' . substr($phone, -4);
                $new_user->inviter = $user['id'];
                $new_user_res = $new_user->save();
                if (!$new_user_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }
                $to_user_id = $new_user_res->id;
            }
        }
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $check_code->status = 1;
        $check_code->exchange_time = $now_date;
        $check_code->phone = $phone;
        $check_code->user_id = $user['id'];
        $check_code->to_user_id = $to_user_id;
        $check_code->os_type = $os_type;
        $code_res = $check_code->save();

        if (!$code_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        if ($check_code->is_new_code == 1) {
            $r_res = $this->toRedeem($check_code, $to_user_id);
        } else {
            //todo 兑换过程
            $r_res = $this->toRedeemOld($check_code, $to_user_id);
        }
        if ($r_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function toRedeemOld($code, $user_id)
    {
        return false;
    }

    public function toRedeem($code, $user_id)
    {
        $use_type = intval($code->redeem_type);
        $product_id = intval($code->goods_id);
        $now_date = date('Y-m-d H:i:s');
        switch ($use_type) {
            case 1:
                $col_info = Column::where('user_id', '=', $product_id)->first();
                $check_sub = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $col_info->id)
                    ->where('type', '=', 1)
                    ->first();
                if (empty($check_sub)) {
                    $temp_data['type'] = 1;
                    $temp_data['user_id'] = $user_id;
                    $temp_data['relation_id'] = $product_id;
                    $temp_data['start_time'] = $now_date;
                    $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    $temp_data['status'] = 1;
                    $temp_data['give'] = 4;
                    $temp_data['created_at'] = $temp_data['updated_at'] = $now_date;
                    $temp_res = DB::table('nlsg_subscribe')->insert($temp_data);
                    if ($temp_res) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($check_sub->end_time > $now_date) {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time . ' +1 year'));
                    } else {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    }
                    $temp_res = Subscribe::whereId($check_sub->id)
                        ->update($temp_data);
                    if ($temp_res === false) {
                        return false;
                    } else {
                        return true;
                    }
                }
            case 2:
                $check_sub = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $product_id)
                    ->where('type', '=', 6)
                    ->first();
                if (empty($check_sub)) {
                    $temp_data['type'] = 6;
                    $temp_data['user_id'] = $user_id;
                    $temp_data['relation_id'] = $product_id;
                    $temp_data['start_time'] = $now_date;
                    $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    $temp_data['status'] = 1;
                    $temp_data['give'] = 4;
                    $temp_data['created_at'] = $temp_data['updated_at'] = $now_date;
                    $temp_res = DB::table('nlsg_subscribe')->insert($temp_data);
                    if ($temp_res) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($check_sub->end_time > $now_date) {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time . ' +1 year'));
                    } else {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    }
                    $temp_res = Subscribe::whereId($check_sub->id)
                        ->update($temp_data);
                    if ($temp_res === false) {
                        return false;
                    } else {
                        return true;
                    }
                }
            case 3:
                $check_sub = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $product_id)
                    ->where('type', '=', 2)
                    ->first();
                if (empty($check_sub)) {
                    $temp_data['type'] = 2;
                    $temp_data['user_id'] = $user_id;
                    $temp_data['relation_id'] = $product_id;
                    $temp_data['start_time'] = $now_date;
                    $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    $temp_data['status'] = 1;
                    $temp_data['give'] = 4;
                    $temp_data['created_at'] = $temp_data['updated_at'] = $now_date;
                    $temp_res = DB::table('nlsg_subscribe')->insert($temp_data);
                    if ($temp_res) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($check_sub->end_time > $now_date) {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time . ' +1 year'));
                    } else {
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    }
                    $temp_res = Subscribe::whereId($check_sub->id)
                        ->update($temp_data);
                    if ($temp_res === false) {
                        return false;
                    } else {
                        return true;
                    }
                }
        }
    }


    //生成兑换码分组码
    public static function createGroupName()
    {
        $year = intval(date('y'));
        $day = date('z');
        $head = $year . str_pad($day, 3, 0, STR_PAD_LEFT);
        $head = self::get_34_Number($head, 3); //年月日标记 三位
        $i = 0;
        while ($i < 1) {
            $group_name = self::get_34_Number(rand(1, 999), 2);
            $group_name = $head . $group_name;
            $check = self::where('new_group', '=', $group_name)
                ->where('is_new_code', '=', 1)
                ->select(['id'])
                ->first();
            if (!$check) {
                $i++;
            }
        }

        return $group_name;
    }

    //生成34进制数
    public static function get_34_Number($int, $format = 8)
    {
        $dic = array(
            0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9',
            10 => 'a', 11 => 'b', 12 => 'c', 13 => 'd', 14 => 'e', 15 => 'f', 16 => 'g', 17 => 'h',
            18 => 'j', 19 => 'k', 20 => 'l', 21 => 'm', 22 => 'm', 23 => 'p', 24 => 'q', 25 => 'r',
            26 => 's', 27 => 't', 28 => 'u', 29 => 'v', 30 => 'w', 31 => 'x', 32 => 'y', 33 => 'z',
        );

        $arr = array();
        $loop = true;
        while ($loop) {
            $arr[] = $dic[bcmod($int, 34)];
            $int = floor(bcdiv($int, 34));
            if ($int == 0) {
                $loop = false;
            }
        }
        $arr = array_pad($arr, $format, $dic[0]);
        return implode('', array_reverse($arr));
    }

    public static function createCodeTemp()
    {
        return str_pad(rand(1, 32900000), 5, 0, STR_PAD_LEFT);
    }
}
