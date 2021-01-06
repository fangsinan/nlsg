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

        if ($status !== -1) {
            $query->where('status', '=', $status);
        }

        return $query->select(['id', 'code', 'name', 'status'])
            ->limit($size)
            ->orderBy('status', 'asc')
            ->orderBy('id', 'asc')
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
        //$check_code->user_id = $user['id'];
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
        $code = $code->code;
        //活动编号
        $hd = substr($code, 0, 3);
        //兑换类型   1：兑换优惠券   2：兑换产品
        $deem_type = intval(substr($code, 3, 1));
        //适用范围  1：专栏  2：精品课  3：商品 4会员
        $use_type = intval(substr($code, 4, 1));
        //适用产品
        $product_id = substr($code, 5, 3);

        $user_info = User::whereId($user_id)->first();
        $yard_info = self::where('code', '=', $code)->first();

        DB::beginTransaction();

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $time = 365 * 24 * 3600;
        $end_date = date('Y-m-d', strtotime("+1 year +1 day"));

        $update_code_data['user_id'] = $user_id;
        $update_code_data['phone'] = $user_info->phone;
        $update_code_data['status'] = 1;
        $update_code_data['exchange_time'] = $now_date;
        $update_res = self::where('id', '=', $yard_info->id)
            ->update($update_code_data);
        if ($update_res === false) {
            DB::rollBack();
            return false;
        }

        if ($deem_type === 2) {
            if ($yard_info->id > 343812 && $yard_info->id < 346313) {
                $use_type = 2;
                switch ($product_id) {
                    case 396:
                        $product_id = 531;
                        break; //数学盒子
                    case 395:
                        $product_id = 0;
                        break; //扎染
                    case 394:
                        $product_id = 529;
                        break; //科学盒子基础版
                    case 393:
                        $product_id = 530;
                        break; //益智拼插积木套装
                    case 392:
                        $product_id = 528;
                        break; //艺术启蒙粘土套装
                }
            } elseif (
                ($yard_info->id >= 346313 && $yard_info->id <= 347312) ||
                ($yard_info->id >= 347313 && $yard_info->id <= 348312)
            ) {
                $product_id = substr($product_id, 0, -1); //此精品课是两位数
            }

            switch ($use_type) {
                case 1:
                    //专栏
                    $col_info = Column::whereId($product_id)->first();
                    $check_sub = Subscribe::where('user_id', '=', $user_id)
                        ->where('relation_id', '=', $col_info->id)
                        ->where('type', '=', 1)
                        ->where('end_time', '>', $now_date)
                        ->where('status', '=', 1)
                        ->first();

                    if ($yard_info->id > 357007 && $yard_info->id <= 357508) {
                        $time = 90 * 24 * 3600;
                        $end_date = date('Y-m-d', strtotime("+3 months +1 day"));
                        $end_time = strtotime($end_date);
                    }

                    if (empty($check_sub)) {
                        $add_sub_data['type'] = 1;
                        $add_sub_data['relation_id'] = $col_info->id;
                        $add_sub_data['user_id'] = $user_id;
                        $add_sub_data['end_time'] = $end_date;
                        $add_sub_data['start_time'] = $now_date;
                        $add_sub_data['created_at'] = $now_date;
                        $add_sub_data['exchange_time'] = $now_date;
                        $add_sub_data['give'] = 4;
                        $add_sub_data['status'] = 1;
                        $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                    } else {
                        $add_sub_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time) + $time);
                        $add_sub_data['exchange_time'] = $now_date;
                        $add_sub_data['status'] = 1;
                        $add_res = Subscribe::where('id', '=', $check_sub->id)
                            ->update($add_sub_data);
                    }
                    if ($add_res === false) {
                        DB::rollBack();
                        return false;
                    }
                    DB::commit();
                    return true;
                case 2;
                    //课程 视频是讲座
                    $works_info = Works::whereId($product_id)->first();
                    if ($works_info->type == 1) {
                        //讲座
                        $col_info = Column::where('works_id', '=', $product_id)->where('type', '=', 2)->first();
                        $check_sub = Subscribe::where('user_id', '=', $user_id)
                            ->where('relation_id', '=', $col_info->id)
                            ->where('type', '=', 6)
                            ->where('end_time', '>', $now_date)
                            ->where('status', '=', 1)
                            ->first();
                        if (empty($check_sub)) {
                            $add_sub_data['type'] = 6;
                            $add_sub_data['relation_id'] = $col_info->id;
                            $add_sub_data['user_id'] = $user_id;
                            $add_sub_data['end_time'] = $end_date;
                            $add_sub_data['start_time'] = $now_date;
                            $add_sub_data['created_at'] = $now_date;
                            $add_sub_data['exchange_time'] = $now_date;
                            $add_sub_data['give'] = 4;
                            $add_sub_data['status'] = 1;
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                        } else {
                            $add_sub_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time) + $time);
                            $add_sub_data['exchange_time'] = $now_date;
                            $add_sub_data['status'] = 1;
                            $add_res = Subscribe::where('id', '=', $check_sub->id)
                                ->update($add_sub_data);
                        }
                    } else {
                        $check_sub = Subscribe::where('user_id', '=', $user_id)
                            ->where('relation_id', '=', $product_id)
                            ->where('type', '=', 2)
                            ->where('end_time', '>', $now_date)
                            ->where('status', '=', 1)
                            ->first();
                        if (empty($check_sub)) {
                            $add_sub_data['type'] = 2;
                            $add_sub_data['relation_id'] = $product_id;
                            $add_sub_data['user_id'] = $user_id;
                            $add_sub_data['end_time'] = $end_date;
                            $add_sub_data['start_time'] = $now_date;
                            $add_sub_data['created_at'] = $now_date;
                            $add_sub_data['exchange_time'] = $now_date;
                            $add_sub_data['give'] = 4;
                            $add_sub_data['status'] = 1;
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                        } else {
                            $add_sub_data['end_time'] = date('Y-m-d 23:59:59', strtotime($check_sub->end_time) + $time);
                            $add_sub_data['exchange_time'] = $now_date;
                            $add_sub_data['status'] = 1;
                            $add_res = Subscribe::where('id', '=', $check_sub->id)
                                ->update($add_sub_data);
                        }
                    }
                    if ($add_res === false) {
                        DB::rollBack();
                        return false;
                    }
                    DB::commit();
                    return true;
                default:
                    return false;
            }

        } else {
            //优惠券部分
            $add_coupon_data = [];
            switch ($hd) {
                case 100:
                case 101:
                case 0:
                    $add_coupon_data['full_cut'] = 100;
                    $add_coupon_data['price'] = 10;
            }
            switch ($product_id) {
                case 102:
                    $add_coupon_data['full_cut'] = 199;
                    $add_coupon_data['price'] = 10;
                    break;
                case 103:
                    $add_coupon_data['full_cut'] = 99;
                    $add_coupon_data['price'] = 5;
                    break;
            }
            //适用范围  1：专栏  2：精品课  3：商品
            switch ($use_type) {
                case 1:
                    $add_coupon_data['type'] = 1;
                    $add_coupon_data['name'] = '专栏兑换优惠券活动';
                    break;
                case 2:
                    $add_coupon_data['type'] = 5;
                    $add_coupon_data['name'] = '课程兑换优惠券活动';
                    break;
                case 3:
                    $add_coupon_data['type'] = 3;
                    $add_coupon_data['name'] = '商城兑换优惠券活动';
                    break;
            }
            $add_coupon_data['user_id'] = $user_id;
            $add_coupon_data['begin_time'] = $now_date;
            $add_coupon_data['end_time'] = date('Y-m-d 23:59:59', strtotime("+7 days"));
            $add_coupon_data['status'] = 1;
            $add_coupon_data['get_way'] = 1;
            $add_coupon_data['number'] = Coupon::createCouponNum(2, 0);

            $coupon_res = DB::table('nlsg_coupon')->insert($add_coupon_data);
            if ($coupon_res === false) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        }

    }

    public function toRedeem($code, $user_id)
    {
        $use_type = intval($code->redeem_type);
        $product_id = intval($code->goods_id);
        $now_date = date('Y-m-d H:i:s');
        switch ($use_type) {
            case 1:
                $col_info = Column::where('user_id', '=', $product_id)->where('type', '=', 1)->first();
                $check_sub = Subscribe::where('user_id', '=', $user_id)
                    ->where('relation_id', '=', $col_info->id)
                    ->where('type', '=', 1)
                    ->first();
                if (empty($check_sub)) {
                    $temp_data['type'] = 1;
                    $temp_data['user_id'] = $user_id;
                    $temp_data['relation_id'] = $col_info->id;
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
            case 2:
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
