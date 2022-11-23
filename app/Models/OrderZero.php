<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class OrderZero extends Base
{

    protected $table = 'nlsg_order_zero';

    protected $fillable = [
        'id', 'relation_id','user_id','status','pay_time',
        'ordernum', 'ip', 'pay_type',  'os_type','twitter_id', 'remark', 'live_admin_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function twitter()
    {
        return $this->belongsTo(User::class, 'twitter_id', 'id');
    }

    public function relationLiveInfo(): HasOne
    {
        return $this->hasOne(Live::class,'id','relation_id');
    }

    public function fromLiveInfo(): HasOne
    {
        return $this->hasOne(Live::class,'id','live_id');
    }

    /**
     * checkZeroLive 0元购直播处理
     *
     * @param $liveId
     * @param $uid
     * @param array $form_data
     *
     * @return array|bool
     */
    static function checkZeroLive($liveId, $uid, array $form_data = [])
    {
        $live = Live::where(['id'=>$liveId,'is_zero'=>2])->first();
        if(empty($live)){
            return false;
        }
        // 如果是0元购   写入新表， 如果当前用户添加了企业微信   则将推荐人存储  否则失效
        $unionid = User::where("id",$uid)->value("unionid");
        $wechat_user = UserWechat::where(['unionid'=>$unionid])->first();
        if(!empty($wechat_user)){
            //存在企业微信关系   不需要传渠道商  为自主用户
            $form_data['twitter_id'] = 0;
        }

        $time = date("Y-m-d H:i:s");
        $orderNum = MallOrder::createOrderNumber($uid, 3);

        DB::beginTransaction();
        $order_zero_id = OrderZero::insertGetId([
            "relation_id"   => $liveId,
            "live_id"       => $form_data['form_liveId']??0,
            "user_id"       => $uid,
            "status"        => 1,
            "pay_time"      => $time,
            "ordernum"      => $orderNum,
            "ip"            => $form_data['ip']??"",
            "pay_type"      => $form_data['pay_type']??0,
            "os_type"       => $form_data['os_type']??0,
            "twitter_id"    => $form_data['twitter_id']??0,
            "remark"        => $form_data['remark']??"",
            "live_admin_id" => $form_data['live_admin_id']??0,
        ]);


        $startTime = strtotime(date('Y-m-d', time()));
        $endTime = strtotime(date('Y', $startTime) + 1 . '-' . date('m-d', $startTime)) + 86400; //到期日期
        $sub_res = Subscribe::insert([
            'user_id'       => $uid, //会员id
            'pay_time'      => $time, //支付时间
            'type'          => 3,
            'order_id'      => $order_zero_id, //订单id
            'status'        => 1,
            'start_time'    => $time,
            'end_time'      => date("Y-m-d H:i:s", $endTime),
            'relation_id'   => $liveId,
            'is_zero'       => 2,
            "twitter_id"    => $form_data['twitter_id']??0,
        ]);

        if($order_zero_id && $sub_res){
            DB::commit();
            return OrderZero::where("id",$order_zero_id)->first();
        }else{
            DB::rollBack();
            return [];
        }
    }
}
