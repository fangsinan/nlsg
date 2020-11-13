<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class Order extends Base
{

    protected $table = 'nlsg_order';

    protected $fillable = [ 'id','live_num','pay_type','share_code','activity_tag','kun_said','refund_no','is_live_order_send',
        'ordernum', 'status', 'type', 'user_id', 'relation_id', 'cost_price', 'price', 'twitter_id', 'coupon_id', 'ip',
        'os_type', 'live_id', 'reward_type','reward','service_id','reward_num','pay_time','start_time','end_time','pay_price','city','vip_order_type',
        'send_type','send_user_id','remark','tweeter_code',


    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //下单check
    public function addOrderCheck($user_id, $tweeter_code, $target_id, $type)
    {

        //校验用户等级
        $rst = User::getLevel($user_id);
        if ($rst > 2) {
            return ['code' => 0, 'msg' => '您已是vip用户,可免费观看'];
        }

        //校验下单用户是否关注
        $is_sub = Subscribe::isSubscribe($user_id, $target_id, $type);
        if ($is_sub) {
            return ['code' => 0, 'msg' => '您已订阅过'];
        }

        //校验推客信息有效
        $tweeter_level = User::getLevel($tweeter_code);
        if ($tweeter_level > 0) {
            //推客是否订阅
            $is_sub = Subscribe::isSubscribe($tweeter_code, $target_id, $type);
            if ($is_sub == 0) {
                $tweeter_code = 0;
            }
        } else {
            $tweeter_code = 0;
        }
        return ['code' => 1, 'tweeter_code' => $tweeter_code];

    }

    static function getInfo($type,$relation_id,$send_type,$user_id=0){
        $result = false;
        switch ($type) {
            case 1:
                $model = new Column();
                $result = $model->getIndexColumn([$relation_id]);
                break;
            case 9:
                $model = new Works();
                $result = $model->getIndexWorks([$relation_id], 2,$user_id);
                break;
            case 10:
                $result = Live::where(['id'=>$relation_id])->get()->toArray();
                break;
            case 14:
                $result = OfflineProducts::where(['id'=>$relation_id])->get()->toArray();
                break;
            case 15:
                $model = new Column();
                $result = $model->getIndexColumn([$relation_id]);
                break;
            case 16:
                $result[] = ['id'=>1,'type' => 6, 'text'=>'幸福360会员','img'=>'/nlsg/poster_img/1581599882211_.pic.jpg','price'=>360.00];
                break;
            case 17:
                if($send_type == 1 || $send_type == 2){
                    $model = new Column();
                    $result = $model->getIndexColumn([$relation_id]);
                }else if($send_type == 3 || $send_type == 4){
                    $model = new Works();
                    $result = $model->getIndexWorks([$relation_id], 2,$user_id);
                }
                break;
        }
        return $result;
    }

}
