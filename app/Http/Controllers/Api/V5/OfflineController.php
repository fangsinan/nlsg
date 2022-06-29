<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\LiveCountDown;
use App\Models\LiveInfo;
use App\Models\MallOrder;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfflineController extends Controller
{



    /**
     * {get} api/v5/offline/info  线下课程详情
     * @apiVersion 4.0.0
     * @apiName  info
     * @apiParam {number} id   线下课id
     */
    public function getOfflineInfo(Request $request)
    {
        $id = $request->get('id');
        $uid = $this->user['id'] ?? 0;
        $validator = Validator::make($request->input(), [
            'id' => 'required|numeric',
            // 'info_id' => 'bail:required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first(),(object)[]);
        }
        // 如果视频url存在  就显示视频
        $model = new OfflineProducts();
        $result = $model->getOfflineProducts([$id]);
        if ( empty($result) ) {
            return $this->error(1000,'没有数据',(object)[]);
        }
        $info = $result[0];
        

        $types = FuncType(config('web.GlobalType.INPUT_TYPE.OfflineType'));
        $info['is_sub'] = Subscribe::isSubscribe($uid,$id,$types['sub_type']);
        $info['teacher_data'] = User::getTeacherInfo($info['user_id']);
        // 老师信息
        $info['is_follow'] = UserFollow::IsFollow($uid,$info['user_id']);
        $info['price'] = (string)intval($info['price']);
        return success($info);
    }





    /**
     * {post} /api/v5/order/create_products_order 线下课下单
     * @apiName create_products_order
     *
     * @apiParam {int} product_id      线下产品id
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     * @apiParam {int} inviter 推客id
     *
     */
    public function createProductsOrder(Request $request)
    {
        $product_id = $request->input('product_id', 1);   //目标id
        $os_type = $request->input('os_type', 0);
        $pay_type = $request->input('pay_type', 0);
        $live_id = $request->input('live_id', 0);
        $tweeter_code = $request->input('inviter', 0);  //推客id
        $num = $request->input('num', 1);  //
        $user_id = $this->user['id'];
      
        //限制其下单业务
        $checkAddOrder = Order::CheckAddOrder($product_id,14,$this->user,$os_type,$live_id);
        if($checkAddOrder['code'] !== true){
            return $this->error($checkAddOrder['code'], $checkAddOrder['msg']);
        }
        if( $tweeter_code > 0 && $live_id > 0 ){  //需要校验推客id
            $info = LiveInfo::where(['live_pid'=>$live_id])->first();
            $count_data = LiveCountDown::where(['user_id'=>$user_id,'live_id'=>$info['id']])->first();
            if($count_data['new_vip_uid']){
                $tweeter_code = $count_data['new_vip_uid'];  //推客id 为邀约人id
            }
        }
        $ProductInfo = OfflineProducts::find($product_id);
        //检测下单参数有效性
        if (empty($ProductInfo)) {
            return $this->error(0, '产品id有误');
        }

        if($product_id==10){ //限制每个用户只能买一单
            if($num>1){
                return $this->error(0, '每个用户限购一本');
            }
            $OrderPayInfo=Order::query()->where(['user_id' => $user_id,'type'=>14,'relation_id' => $product_id,'status'=>1])->first();
            if(!empty($OrderPayInfo)){
                if(isset($this->user['is_test_pay']) && $this->user['is_test_pay']==0){ //刷单用户可购买多单
                    return $this->error(0, '每个用户限购一本');
                }
            }
        }

        $price = $ProductInfo['price'];

        $liveIdArr=explode(",",$live_id); //处理渠道分享导致"live_id":"311,311,311,311"
        $liveIdNum=count($liveIdArr);
        if($liveIdNum>1){
            $live_id=$liveIdArr[0];
        }

        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 14,
            'user_id' => $user_id,
            'relation_id' => $product_id,
            'cost_price' => $price,
            'price' => ($price*$num),
            'ip' => $this->getIp($request),
            'os_type' => $os_type,
            'pay_type' => $pay_type,
            'live_id' => $live_id ?? 0,
            'live_num' => $num,
            'twitter_id' => $tweeter_code,
        ];

        $order = Order::firstOrCreate($data);
        return success($order['id']);
    }
}