<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\BackendLiveRole;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscribeController extends ControllerBackend
{

    /**
     * @api {get} api/live_v4/sub/index 预约列表
     * @apiVersion 4.0.0
     * @apiName  sub/index
     * @apiGroup 直播后台-评论列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/sub/index
     * @apiDescription  预约列表
     *
     * @apiParam {number} page      分页
     * @apiParam {string} ordernum  订单号
     * @apiParam {string} title     直播标题
     * @apiParam {string} phone     用户账号
     * @apiParam {string} twitter_phone     推客账号
     * @apiParam {string} date      支付时间
     * @apiParam {string} created_at    下单时间
     *
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function index(Request $request)
    {
        $title = $request->get('title') ?? '';
        $ordernum = $request->get('ordernum') ?? '';
        $phone = $request->get('phone') ?? '';
        $date = $request->get('date') ?? '';
        $created_at = $request->get('created_at') ?? '';
        $now_date = date('Y-m-d H:i:s');
        $twitter_phone = $request->input('twitter_phone','');


        //1、查询是否有搜索手机号或 id
        //2、2~3个表查数据
        //3、组合最后的各个查询数据
        if( !empty($phone) ){
            $phoneUser = User::select('id')->where('phone',$phone)->first();
        }
        if( !empty($twitter_phone) ){//推客的id
            $twitter_phoneUser = User::select('id')->where('phone',$twitter_phone)->first();
        }


        $query = Subscribe::with([
            //'user:id,nickname,phone',
            'live:id,title,price,twitter_money,is_free',
            //'order.pay_record_detail:id,type,ordernum,user_id,price',
            //'order.pay_record_detail.user:id,phone,nickname',
            'order:id,ordernum,pay_price,pay_time,twitter_id,pay_type,os_type,created_at'
        ]);

        if ($this->user['role_id'] == 13 && $this->user['live_role_button'] == 2){
            $query->has('order');
        }

        if($this->user['live_role'] == 21){
            $live_user_id = $this->user['user_id'];
            $query->whereHas('live',function($q)use($live_user_id){
                $q->where('user_id','=',$live_user_id)->where('id','>',52);
            });
        }elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $query->whereHas('live', function ($q) use ($son_user_id) {
                $q->whereIn('user_id', $son_user_id)->where('id','>',52);
            });
        }

        if(!empty($title)){
            $query->whereHas('live', function ($q) use($title){
                $q->where('title', 'like', '%'.$title.'%');
            });
        }
        if(!empty($ordernum)){
            $query->whereHas('order', function ($q) use($ordernum){
                $q->where('ordernum', $ordernum);
            });
        }
        if(!empty($twitter_phoneUser)){
            if($this->user['username'] == 13522223779){
                $query->where('twitter_id',$twitter_phoneUser['id']);
            }else{
                $query->whereHas('order', function ($q) use($twitter_phoneUser){
                    $q->where('twitter_id', $twitter_phoneUser['id']);
                });
            }

        }

        if(!empty($date)){
            $query->whereHas('order', function ($q) use($date,$now_date){
                $q->where('pay_time','>=', $date[0]);
                if (empty($date[1] ?? '')) {
                    $date[1] = $now_date;
                }
                $q->where('pay_time','<', $date[1]);
            });
        }

        $query->select('id', 'type', 'user_id', 'relation_id', 'pay_time','order_id','created_at','twitter_id')
            ->where('is_del',0)
            ->where('status',1)
            ->where('type',3);

        //sub创建时间
        if(!empty($created_at)){
            $query->where('created_at','>=', $created_at[0]);
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            }
            $query->where('created_at','<', $created_at[1]);
        }


        if( !empty($phoneUser) ) {
            $query->where('user_id',$phoneUser['id']);
        }


        $lists = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();

        $ordernum = [];
        $user_ids = [];
        foreach ($lists['data'] as &$val){
            $val['twitter'] = [];
            $twitter_id = $val['order']['twitter_id'] ?? 0;
            //免费的邀约人是live_count_down
            if( $val['live']['is_free'] == 1 ){
                $twitter_id = $val['twitter_id'];
            }

            if(!empty($twitter_id)){
                $twitter = User::find($twitter_id);
                $val['twitter']['phone'] = $twitter['phone'];
                $val['twitter']['nickname'] = $twitter['nickname'];
            }

            //ordernum
            if( !empty($val['order']['ordernum']) ){
                $ordernum[] = $val['order']['ordernum'];

            }

            if( !empty($val['user_id']) ){
                $user_ids[] = $val['user_id'];
            }

        }

        //查询剩余信息
        //'user:id,nickname,phone',
        //'order.pay_record_detail:id,type,ordernum,user_id,price',
        //'order.pay_record_detail.user:id,phone,nickname',
        $new_detailes =[];
        $detailes = PayRecordDetail::with(['user:id,phone,nickname',])
            ->select('id', 'type', 'ordernum', 'user_id', 'price')
            ->whereIn('ordernum',$ordernum)->get()->toArray();
        foreach ($detailes as $dk=>$dv){
            $new_detailes[$dv['ordernum']] = $dv;
        }
        $new_users = [];
        $users = User::select('id', 'nickname', 'phone')->whereIn('id',$user_ids)->get()->toArray();
        foreach ($users as $dk=>$dv){
            $new_users[$dv['id']] = $dv;
        }


        foreach ($lists['data'] as &$val){

            if(!empty($val['order']) && !empty($new_detailes[$val['order']['ordernum']])){
                $val['order']['pay_record_detail'] = $new_detailes[$val['order']['ordernum']];
            }

            if(!empty( $new_users[$val['user_id']] )){
                $val['user'] = $new_users[$val['user_id']];
            }

        }

        return success($lists);



//        $query = Subscribe::with([
//            'user:id,nickname,phone',
//            'live:id,title,price,twitter_money',
//            'order.pay_record_detail:id,type,ordernum,user_id,price',
//            'order.pay_record_detail.user:id,phone,nickname',
//            'order:id,ordernum,pay_price,pay_time,twitter_id,pay_type,os_type,created_at'
//        ]);
//
//        if (!empty($twitter_phone)){
//            $query->whereHas('order.pay_record_detail.user',function($q)use($twitter_phone){
//                $q->where('phone','like',"%$twitter_phone%");
//            });
//        }
//
//        if($this->user['live_role'] == 21){
//            $live_user_id = $this->user['user_id'];
//            $query->whereHas('live',function($q)use($live_user_id){
//                $q->where('user_id','=',$live_user_id);
//            });
//        }elseif ($this->user['live_role'] == 23) {
//            $blrModel = new BackendLiveRole();
//            $son_user_id = $blrModel->getDataUserId($this->user['username']);
//            $query->whereHas('live', function ($q) use ($son_user_id) {
//                $q->whereIn('user_id', $son_user_id);
//            });
//        }
//
//        if(!empty($phone)){
//            $query->whereHas('user', function ($q) use($phone){
//                $q->where('phone', $phone);
//            });
//        }
//        if(!empty($title)){
//            $query->whereHas('live', function ($q) use($title){
//                $q->where('title', 'like', '%'.$title.'%');
//            });
//        }
//        if(!empty($ordernum)){
//            $query->whereHas('order', function ($q) use($ordernum){
//                $q->where('ordernum', $ordernum);
//            });
//        }
//        if(!empty($date)){
//            $query->whereHas('order', function ($q) use($date,$now_date){
////                $date = explode(',', $date);
//                $q->where('pay_time','>=', $date[0]);
//                if (empty($date[1] ?? '')) {
//                    $date[1] = $now_date;
//                }
//                $q->where('pay_time','<', $date[1]);
//            });
//        }
////        if(!empty($created_at)){
////            $query->whereHas('order', function ($q) use($created_at,$now_date){
////                $created_at = explode(',', $created_at);
////                $q->where('pay_time','>=', $created_at[0]);
////                if (empty($created_at[1] ?? '')) {
////                    $created_at[1] = $now_date;
////                }
////                $q->where('pay_time','<', $created_at[1]);
////            });
////        }
//
//        $query->select('id', 'type', 'user_id', 'relation_id', 'pay_time','order_id','created_at')
//            ->where('is_del',0)
//            ->where('status',1)
//            ->where('type',3);
//
//
//        //sub创建时间
//        if(!empty($created_at)){
////            $created_at = explode(',', $created_at);
//            $query->where('created_at','>=', $created_at[0]);
//            if (empty($created_at[1] ?? '')) {
//                $created_at[1] = $now_date;
//            }
//            $query->where('created_at','<', $created_at[1]);
//        }
//        $lists = $query->orderBy('created_at', 'desc')
//            ->paginate(10)
//            ->toArray();
//
//        foreach ($lists['data'] as &$val){
//            $val['twitter'] = [];
//            if(!empty($val['order']['twitter_id'])){
//                $twitter = User::find($val['order']['twitter_id']);
//                $val['twitter']['phone'] = $twitter['phone'];
//                $val['twitter']['nickname'] = $twitter['nickname'];
//            }
//        }
//
//
//        return success($lists);

    }
}
