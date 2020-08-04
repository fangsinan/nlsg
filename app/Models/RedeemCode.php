<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class RedeemCode extends Base
{
    protected $table = 'nlsg_redeem_code';

    //todo 兑换
    public function redeem($params, $uid)
    {

        $code = $params['code']??'';
        $phone = $params['phone']??'';
        if(empty($code)){
            return ['code'=>false,'msg'=>'参数错误'];
        }


        if(empty($phone)){
            $to_user_id = $uid;
        }else{
            $check_phone = User::where('phone','=',$phone)->first();
            if($check_phone){
                $to_user_id = $check_phone->id;
            }else{

            }
        }



        return $this->success(['code' => true, 'msg' => '兑换xxx成功']);


    }
}
