<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class RedeemCode extends Base
{
    protected $table = 'nlsg_redeem_code';

    //兑换
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
            return ['code' => false, 'msg' => '失败'];
        }

        //todo 兑换过程
        $this->toRedeem($check_code,$to_user_id);


        return $this->success(['code' => true, 'msg' => '兑换xxx成功']);


    }

    public function toRedeem($code,$user_id){
        $use_type = intval($code->redeem_type);
        $product_id = intval($code->goodes_id);

        switch ($use_type){
            case 1:
                break;
            case 2:
                break;
        }


    }
}
