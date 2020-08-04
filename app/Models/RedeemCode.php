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





        return $this->success(['code' => true, 'msg' => '兑换xxx成功']);


    }
}
