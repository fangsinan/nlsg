<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class RedeemCode extends Base
{
    protected $table = 'nlsg_redeem_code';

    //todo 兑换
    public function redeem($params, $user)
    {

        $code = $params['code'] ?? '';
        $phone = $params['phone'] ?? '';
        $os_type = $params['os_type'] ?? 0;

        if (!in_array($os_type, [1, 2, 3])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (empty($code)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_code = RedeemCode::where('code', '=', $code)
            ->where('status', '=', 0)
            ->where('can_use', '=', 1)
            ->first();
        if (!$check_code) {
            return ['code' => false, 'msg' => '无效兑换码'];
        }

        DB::beginTransaction();

        if (empty($phone)) {
            $to_user_id = $user->id;
            $phone = $user->phone;
        } else {

            $check_phone = User::where('phone', '=', $phone)->first();
            if ($check_phone) {
                $to_user_id = $check_phone->id;
            } else {
                $user = new User();
                $user->phone = $phone;
                $user->nickname = substr($phone, 0, 3) . '****' . substr($phone, -4);
                $user_res = $user->save();
                if (!$user_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }
                $to_user_id = $user->id;
            }
        }
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $check_code->status = 1;
        $check_code->exchange_time = $now_date;
        $check_code->phone = $phone;
        $check_code->user_id = $user->id;
        $check_code->to_user_id = $to_user_id;
        $check_code->os_type = $os_type;
        $code_res = $check_code->save();
        if (!$code_res) {
            return ['code' => false, 'msg' => '失败'];
        }

        //todo 兑换过程


        return $this->success(['code' => true, 'msg' => '兑换xxx成功']);


    }
}
