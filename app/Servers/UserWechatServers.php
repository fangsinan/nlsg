<?php


namespace App\Servers;


use App\Jobs\jobOfCytx;
use App\Jobs\JobOfSocket;
use App\Models\UserWechat;
use App\Models\UserWechatFollow;
use App\Models\UserWechatName;
use App\Models\UserWechatTransfer;
use App\Models\UserWechatTransferLog;
use App\Models\UserWechatTransferRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Libraries\ImClient;
use Predis\Client;

class UserWechatServers
{
    public $token;

    public function __construct()
    {
        $this->token = $this->getAccess_token();
    }

//    public function test(){
//
//        $data = [
//            'handover_userid' => "WangShaoWei",
//            'takeover_userid' => "HanJian",
//            'cursor' => '',
//        ];
//
//        $detail_res = ImClient::curlPost('https://qyapi.weixin.qq.com/cgi-bin/externalcontact/transfer_result?access_token=' . $this->token, json_encode($data));
//
//        var_dump($detail_res);
//    }

    /**
     * @return mixed
     * 获取token
     */
    public function getAccess_token()
    {

        $key = 'redis_wechat_user_access_token';
        $token = Redis::get($key);
        if (empty($token)) {
            $corpid = "wwb4a68b6963803c46";
            $Secret = "RB7XUdI7hZy8Y7hDgJ0cw5BqeULPZK0FBgvljcrsY8Q";
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$Secret";
            $res = ImClient::curlGet($url);
            $res = json_decode($res, true);
            $token = $res['access_token'];
            Redis::setex($key, 7200, $token);
        }

        return $token;
    }

    /**
     * @return bool|mixed
     * 接口拉取员工列表
     */
    public function get_follow_user_list()
    {

        $getDetail_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get_follow_user_list?access_token=" . $this->token;
        $detail_res = ImClient::curlGet($getDetail_url);
        $detail_res = json_decode($detail_res, true);
        if ($detail_res['errcode'] == 0) {
            return $detail_res['follow_user'];
        }
        return false;
    }

    /**
     * @param $userid
     * 获取员工下客户列表
     */
    public function get_user_list($userid)
    {

        $getDetail_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/list?access_token=" . $this->token . '&userid=' . $userid;
        $detail_res = ImClient::curlGet($getDetail_url);
        $detail_res = json_decode($detail_res, true);

        if ($detail_res['errcode'] == 0) {
            return $detail_res['external_userid'];
        }

        return false;

    }

    /**
     * @param $external_userid
     * @return bool|mixed
     * 获取客户详情
     */
    public function get_user_info($external_userid,$follow_user_userid='')
    {

        $getDetail_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get?access_token=" . $this->token . '&external_userid=' . $external_userid;

        $detail_res = ImClient::curlGet($getDetail_url);


        $detail_res = json_decode($detail_res, true);

        if ($detail_res['errcode'] == 0) {


            $UserWechat = UserWechat::query()->where('external_userid', $external_userid)->first();

            $follow_user_arr=$detail_res['follow_user'];


            if ($UserWechat) {

                //获取销售列表

                $saleArr=UserWechatName::query()->where('is_sale',2)->pluck('follow_user_userid')->toArray();

                $first_follow_user=[];

                $farmat_follow_user=[];
                foreach ($follow_user_arr as $val){
                    $farmat_follow_user[$val['userid']]=$val;
                    if(empty($first_follow_user) && in_array($val['userid'],$saleArr)){
                        $first_follow_user=$val;
                    }
                }


                if($follow_user_userid && isset($farmat_follow_user[$follow_user_userid])){
                    //分配员工
                    $follow_user=$farmat_follow_user[$follow_user_userid];

                }elseif(empty($follow_user) && isset($farmat_follow_user[$UserWechat->source_follow_user_userid])){
                    //来源员工
                    $follow_user=$farmat_follow_user[$UserWechat->source_follow_user_userid];

                } elseif(empty($follow_user) && isset($farmat_follow_user[$UserWechat->follow_user_userid])){
                    //当前用户所属员工
                    $follow_user=$farmat_follow_user[$UserWechat->follow_user_userid];

                }elseif (empty($follow_user) && $first_follow_user){

                    //最早添加的业务员企业微信
                    $follow_user=$first_follow_user;

                }

                if(empty($follow_user)){
                    //第一个员工
                    $follow_user=$follow_user_arr[0];
                }

            }else{

                $follow_user=$follow_user_arr[0];

                $UserWechat = new UserWechat();
                $created_at = date('Y-m-d H:i:s', $follow_user['createtime']);
                $UserWechat->created_at = $created_at;
                $UserWechat->source_follow_user_tags_add_way = $follow_user['add_way'] ?? '';
                $UserWechat->source_follow_user_userid = $follow_user['userid'] ?? '';
            }

            $UserWechat->follow_user_userid = $follow_user['userid'] ?? '';
            $UserWechat->follow_user_remark = $follow_user['remark'] ?? '';
            $UserWechat->follow_user_description = $follow_user['description'] ?? '';
            $UserWechat->follow_user_createtime = $follow_user['createtime'] ?? '';
            $UserWechat->follow_user_tags_oper_userid = $follow_user['oper_userid'] ?? '';
            $UserWechat->follow_user_tags_add_way = $follow_user['add_way'] ?? '';

            $UserWechat->external_userid = $detail_res['external_contact']['external_userid'] ?? "";
            $UserWechat->name = $detail_res['external_contact']['name'] ?? "";
            $UserWechat->type = $detail_res['external_contact']['type'] ?? "";
            $UserWechat->avatar = $detail_res['external_contact']['avatar'] ?? "";
            $UserWechat->gender = $detail_res['external_contact']['gender'] ?? "";
            $UserWechat->unionid = $detail_res['external_contact']['unionid'] ?? "";

            $UserWechat->save();

            $detail_res['external_contact']['follow_user'] = $detail_res['follow_user'];

            return $detail_res['external_contact'];
        }

        return false;
    }

    /**
     * @param $userid_list 员工userid 数组
     * 批量获取客户详情
     */
    public function batch_get_by_user($userid_list, $cursor = "")
    {

        if (!is_array($userid_list)) {
            $userid_list = [$userid_list];
        }

        $data = [
            "userid_list" => $userid_list,
            "limit" => 100,
            "cursor" => $cursor,
        ];

        $detail_res = ImClient::curlPost('https://qyapi.weixin.qq.com/cgi-bin/externalcontact/batch/get_by_user?access_token=' . $this->token, json_encode($data));
        $detail_res = json_decode($detail_res, true);

        if ($detail_res['errcode'] == 0) {
            return ['external_contact_list' => $detail_res['external_contact_list'], 'next_cursor' => $detail_res['next_cursor']];
        }

        return false;
    }


    /**
     * @param $data
     * 分配在职成员的客户
     */
    public function transfer_customer($data)
    {

        if (empty($data['takeover_userid'])) {
            return '被分配的企业成员userid不能为空';
        }

        $staff_user = UserWechatName::query()->where('follow_user_userid', '=', $data['takeover_userid'])->first();
        if (empty($staff_user)) {
            return '被分配的企业成员不存在';
        }

        if (empty($data['userids'])) {
            return '客户不能为空';
        }

        $userids_arr = explode(',', $data['userids']);
        if (empty($userids_arr)) {
            return '客户不能为空';
        }

        $user_list = UserWechat::query()->whereIn('id', $userids_arr)
            ->where('follow_user_userid','<>',$data['takeover_userid'])
            ->where('transfer_status', '<>', UserWechat::TRANSFER_STATUS_WAIT)
            ->get(['id', 'external_userid','follow_user_userid'])->toArray();

        $user_list_count = count($user_list);

        if (empty($user_list_count)) {
            return '请选择转移客户';
        }

        if ($user_list_count > 500) {
            return '每次最多分配500个客户';
        }

        DB::beginTransaction();

        //添加转移记录
        $UserWechatTransferRecord = new UserWechatTransferRecord();
        $UserWechatTransferRecord->takeover_userid = $staff_user->follow_user_userid;
        $UserWechatTransferRecord->total = $user_list_count;
        $res = $UserWechatTransferRecord->save();
        if (!$res) {
            DB::rollBack();
            return '操作失败';
        }

        $format_user_list=[];
        foreach ($user_list as $user){
            $format_user_list[$user['follow_user_userid']][$user['external_userid']]=$user;
        }

        $res=UserWechat::query()->whereIn('external_userid', array_column($user_list,'external_userid'))
            ->update(['transfer_record_id'=>$UserWechatTransferRecord->id,'transfer_start_time'=>date('Y-m-d H:i:s'),'transfer_status' => UserWechat::TRANSFER_STATUS_WAIT, 'updated_at' => date('Y-m-d H:i:s')]);

        if(!$res){
            DB::rollBack();
            return '操作失败';
        }

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);

        $Redis->rpush('user_wechat_transfer_customer',json_encode([
            'transfer_record_id'=>$UserWechatTransferRecord->id,
            'format_user_list'=>$format_user_list,
            'takeover_userid'=>$data['takeover_userid']
        ]));

        DB::commit();

        return true;
    }

    /**
     * 消费redis转移客户队列
     */
    public function consume_redis_transfer_customer(){

        add_log('consume_redis_transfer_customer-1','开始请求客户转移');

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        while ($msg = $Redis->rPop('user_wechat_transfer_customer')) {

            add_log('consume_redis_transfer_customer-2',$msg);

            $data=json_decode($msg,true);

            $transfer_record_id=$data['transfer_record_id'];
            $takeover_userid=$data['takeover_userid'];
            $format_user_list=$data['format_user_list'];

            foreach ($format_user_list as $handover_userid=>$user_list){
                $this->transfer_customer_api($transfer_record_id,$handover_userid,$takeover_userid,$user_list);
            }
        }
        add_log('consume_redis_transfer_customer-3','执行完成');

    }



    public function transfer_customer_api($transfer_record_id,$handover_userid,$takeover_userid,$user_list){

        $staff_user = UserWechatName::query()->where('follow_user_userid', $takeover_userid)->first();


        //查询是否存在客户转移任务
        $UserWechatTransfer = UserWechatTransfer::query()->where('handover_userid', $handover_userid)->where('takeover_userid', $takeover_userid)->first();
        if (!$UserWechatTransfer) {
            $UserWechatTransfer = new UserWechatTransfer();
        }
        $UserWechatTransfer->handover_userid = $handover_userid;
        $UserWechatTransfer->takeover_userid = $takeover_userid;
        $UserWechatTransfer->status = UserWechatTransfer::STATUS_WAIT;//等待接替
        $res = $UserWechatTransfer->save();

        if (!$res) {
            DB::rollBack();
            return '操作失败';
        }

        $external_userid_arr=array_column($user_list, 'external_userid');

        $external_userid_chunk=array_chunk($external_userid_arr,100);


        foreach ($external_userid_chunk as $external_userid){

            //调用企业微信接口
            $data = [
                'handover_userid' => $handover_userid,
                'takeover_userid' => $takeover_userid,
                'external_userid' => $external_userid,
                "transfer_success_msg" => "您好，您的服务已升级，后续将由我的同事" . $staff_user->qw_name . "接替我的工作，继续为您服务。"
            ];

            $detail_res = ImClient::curlPost('https://qyapi.weixin.qq.com/cgi-bin/externalcontact/transfer_customer?access_token=' . $this->token, json_encode($data));

//            var_dump($detail_res);

            add_log('transfer_customer_api','迁移微信客户', $detail_res);

            $detail_res = json_decode($detail_res, true);

            if ($detail_res['errcode'] != 0) {
                DB::rollBack();
                return false;
            }

            foreach ($detail_res['customer'] as $customer) {

                $save_data = [

                    'transfer_id' => $UserWechatTransfer->id,
                    'transfer_record_id' => $transfer_record_id,

                    'user_wechat_id' => $user_list[$customer['external_userid']]['id'],
                    'external_userid' => $customer['external_userid'],


                    'handover_userid' => $handover_userid,

                    'takeover_userid' => $takeover_userid,
                    'takeover_time' => time(),

                    'created_at' => date('Y-m-d H:i:s'),

                    'updated_at' => date('Y-m-d H:i:s'),

                ];


                if (!in_array($customer['errcode'],[0,40129])) {

                    //转移失败
                    $save_data['status'] = -1;

                    //修改客户的接替状态为 等待接替
                    UserWechat::query()->where('external_userid', $customer['external_userid'])->update([
                        'transfer_status' => UserWechat::TRANSFER_STATUS_FAIL,//分配失败
                        'updated_at' => date('Y-m-d H:i:s'),
                        'errcode'=>$customer['errcode']
                    ]);

                }

                $save_data['errcode'] = $customer['errcode'];

                $save_data['return_info'] = json_encode($customer);

                DB::table(UserWechatTransferLog::DB_TABLE)->insert($save_data);

            }
        }


        DB::commit();

        return true;
    }


    /**
     * 查询分配结果 定时任务 每小时一次
     */
    public function transfer_result()
    {

       add_log('transfer_result',  'start',date('Y-m-d H:i:s') );

        //查询需要监测的转移客户任务
        $list = UserWechatTransfer::query()->where('status', UserWechatTransfer::STATUS_WAIT)->get();

        add_log('transfer_result',  '查询需要监测的转移客户任务',json_encode($list) );

        DB::beginTransaction();

        foreach ($list as $transfer) {

            $res = $this->transfer_result_api($transfer, $transfer->next_cursor);

            Log::channel('wechat')->info('transfer_result_api_res:   ' . $res);

            if (!$res) {
                DB::rollBack();
                return false;
            }
        }

        DB::commit();

        add_log('transfer_result',  'end',date('Y-m-d H:i:s'));

        return true;
    }

    /**
     * @param UserWechatTransfer $transfer
     * @param $cursor
     * @return bool
     * 查询客户转移结果
     */
    public function transfer_result_api(UserWechatTransfer $transfer, $cursor)
    {

        $transfer_id = $transfer->id;
        $handover_userid = $transfer->handover_userid;
        $takeover_userid = $transfer->takeover_userid;

        $data = [
            'handover_userid' => $handover_userid,
            'takeover_userid' => $takeover_userid,
            'cursor' => $cursor,
        ];

        $detail_res = ImClient::curlPost('https://qyapi.weixin.qq.com/cgi-bin/externalcontact/transfer_result?access_token=' . $this->token, json_encode($data));
        Log::channel('wechat')->info('transfer_result_api:   ' . $detail_res);
        add_log('transfer_result_api', '查询客户转移结果', $detail_res);

        $detail_res = json_decode($detail_res, true);

        var_dump($detail_res);

        $next_cursor = $detail_res['next_cursor'] ?? '';

        if ($detail_res['errcode'] == 0) {

            $is_finished = true;//是否完成转移

            foreach ($detail_res['customer'] as $customer) {

                //如果存在等待转移状态的数据 则表示未完成转移
                if ($customer['status'] == UserWechat::TRANSFER_STATUS_WAIT) {
                    $is_finished = false;
                }

                //同步客户转移状态
                $UserWechat = UserWechat::query()->where('transfer_status', UserWechat::TRANSFER_STATUS_WAIT)->where('external_userid', $customer['external_userid'])->first();
                if ($UserWechat) {
                    $UserWechat->transfer_status = $customer['status'];
                    $res = $UserWechat->save();
                    if (!$res) {
                        return false;
                    }
                }

                //转移成功更新客户信息
                if ($customer['status'] == UserWechat::TRANSFER_STATUS_FINISH && $UserWechat) {

                    //更新微信客户信息
                    $this->get_user_info($customer['external_userid'],$takeover_userid);

                    //修改之前的跟进记录
                    UserWechatFollow::query()->where('external_userid', $customer['external_userid'])->where('status', UserWechatFollow::STATUS_ING)->update([
                        'status' => UserWechatFollow::STATUS_END,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    //添加新的跟进记录
                    $UserWechatFollow = new UserWechatFollow();
                    $UserWechatFollow->user_wechat_id = $UserWechat->id;


                    $UserWechatFollow->external_userid = $customer['external_userid'];
                    $UserWechatFollow->follow_user_userid = $transfer->takeover_userid;
                    $res = $UserWechatFollow->save();
                    if (!$res) {
                        return false;
                    }
                }

                //同步日志转移状态
                $UserWechatTransferLog = UserWechatTransferLog::query()->where('transfer_id', $transfer_id)->where('external_userid', $customer['external_userid'])->where('status', UserWechat::TRANSFER_STATUS_WAIT)->first();

                if ($UserWechatTransferLog) {

                    $UserWechatTransferLog->status = $customer['status'];

                    if (isset($customer['takeover_time'])) {
                        $UserWechatTransferLog->takeover_time = $customer['takeover_time'];
                    }

                    $res = $UserWechatTransferLog->save();
                    if (!$res) {
                        return false;
                    }
                }
            }

            //如果当前接口的数据 都是已完成转移的数据
            if ($is_finished && $transfer->next_cursor == $cursor) {
                $transfer->next_cursor = $next_cursor;
            }

            //更新转移记录的转移状态
            if (UserWechatTransferLog::query()->where('transfer_id', $transfer_id)->where('status', UserWechat::TRANSFER_STATUS_WAIT)->count()) {
                $transfer->status = UserWechatTransfer::STATUS_WAIT;
            } else {
                $transfer->status = UserWechatTransfer::STATUS_FINISH;
            }

            $res = $transfer->save();
            if (!$res) {
                return false;
            }

            //同步转移客户记录状态
            $record_list = UserWechatTransferRecord::query()->where('status', UserWechatTransferRecord::STATUS_WAIT)->get();

            if ($record_list) {

                foreach ($record_list as $record) {

                    //完成转移的数量
                    $record->finish_total = UserWechatTransferLog::query()->where('transfer_record_id', $record->id)->where('status', UserWechat::TRANSFER_STATUS_FINISH)->count();

                    //等待转移的数量
                    $record->wait_total = UserWechatTransferLog::query()->where('transfer_record_id', $record->id)->where('status', UserWechat::TRANSFER_STATUS_WAIT)->count();

                    //判断是否转移完成
                    $log_count = UserWechatTransferLog::query()->where('transfer_record_id', $record->id)->where('status', '<>', UserWechat::TRANSFER_STATUS_WAIT)->count();
                    if ($record->total <= $log_count) {
                        $record->status = UserWechatTransferRecord::STATUS_FINISH;
                    }

                    $record->save();

                }
            }

        } else {

            var_dump($detail_res);
            return false;
        }

        //递归查询下一页
        if ($next_cursor) {
            sleep(1);
            return $this->transfer_result_api($transfer, $next_cursor);
        } else {
            return true;
        }
    }


    /**
     * 清理微信客户数据
     * 1、清楚长时间等待转移的客户
     * 2、清理转移失败的客户
     */
    public function clear_user_wechat_data(){

        $time=date('Y-m-d H:i:s',time()-3*24*60*60);

        $user_list=UserWechat::query()
            ->whereIn('transfer_status',[-1,2])
            ->where('transfer_record_id','>',0)
            ->where('transfer_start_time','<=',$time)->get();

        foreach ($user_list as $user){

            $this->get_user_info($user->external_userid);
            $user->transfer_status=0;
            $user->save();
        }
    }

}
