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
     * @param $external_userid
     * @return bool|mixed
     * 获取客户详情
     */
    public function get_user_info($external_userid)
    {

        $getDetail_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get?access_token=" . $this->token . '&external_userid=' . $external_userid;
        $detail_res = ImClient::curlGet($getDetail_url);


        $detail_res = json_decode($detail_res, true);

        if ($detail_res['errcode'] == 0) {
            $UserWechat = UserWechat::query()->where('external_userid', $external_userid)->first();
            if (!$UserWechat) {
                $created_at = date('Y-m-d H:i:s', $detail_res['follow_user'][0]['createtime']);
                $UserWechat = new UserWechat();
                $UserWechat->created_at = $created_at;
                $UserWechat->source_follow_user_tags_add_way = $detail_res['follow_user'][0]['add_way'] ?? '';
                $UserWechat->source_follow_user_userid = $detail_res['follow_user'][0]['userid'] ?? '';
            }

            $UserWechat->follow_user_userid = $detail_res['follow_user'][0]['userid'] ?? '';
            $UserWechat->follow_user_remark = $detail_res['follow_user'][0]['remark'] ?? '';
            $UserWechat->follow_user_description = $detail_res['follow_user'][0]['description'] ?? '';
            $UserWechat->follow_user_createtime = $detail_res['follow_user'][0]['createtime'] ?? '';
            $UserWechat->follow_user_tags_oper_userid = $detail_res['follow_user'][0]['oper_userid'] ?? '';
            $UserWechat->follow_user_tags_add_way = $detail_res['follow_user'][0]['add_way'] ?? '';

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
     * 批量转移客户
     */
    public function transfer_customer_batch($handover_userid,$takeover_userid,$userids_arr){

        if (empty($handover_userid)) {
            return '企业成员userid不能为空';
        }

        $old_staff_user = UserWechatName::query()->where('follow_user_userid', '=', $handover_userid)->first();
        if (empty($old_staff_user)) {
            return '企业成员不存在';
        }

        if (empty($takeover_userid)) {
            return '被分配的企业成员userid不能为空';
        }

        $staff_user = UserWechatName::query()->where('follow_user_userid', '=', $takeover_userid)->first();
        if (empty($staff_user)) {
            return '被分配的企业成员不存在';
        }
        if ($handover_userid == $takeover_userid) {
            return '被分配的企业成员不能和原来的企业成员相同';
        }

        $user_list = UserWechat::query()->whereIn('id', $userids_arr)
            ->where('follow_user_userid', $handover_userid)
            ->where('transfer_status', '<>', UserWechat::TRANSFER_STATUS_WAIT)
            ->get(['id', 'external_userid'])->toArray();

        $user_list_count = count($user_list);
        if ($user_list_count != count($userids_arr)) {
            return '客户参数错误';
        }

        //添加转移记录
        $UserWechatTransferRecord= new UserWechatTransferRecord();
        $UserWechatTransferRecord->handover_user_id = $old_staff_user->id;
        $UserWechatTransferRecord->takeover_user_id = $staff_user->id;
        $UserWechatTransferRecord->handover_userid = $old_staff_user->follow_user_userid;
        $UserWechatTransferRecord->takeover_userid = $staff_user->follow_user_userid;
        $UserWechatTransferRecord->total =count($userids_arr);
        $res=$UserWechatTransferRecord->save();
        if (!$res) {
            DB::rollBack();
            return '操作失败';
        }

        //批量执行
        $userids_arr_chunk=array_chunk($userids_arr,100);
        $count=count($userids_arr_chunk);

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);

        foreach ($userids_arr_chunk as $k=>$userids){

            $push_data=[
                'transfer_record_id'=>$UserWechatTransferRecord->id,
                'handover_userid'=>$handover_userid,
                'takeover_userid'=>$takeover_userid,
                'userids'=>implode(',',$userids),
            ];

            if($count <= ($k+1)){
                $push_data['is_finish']=1;
            }

            $Redis->rpush('user_wechat_transfer_customer',$push_data);

        }

        return  true;
    }

    /**
     * 消费redis转移客户队列
     */
    public function consume_redis_transfer_customer(){

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        while ($msg = $Redis->rPop('user_wechat_transfer_customer')) {
            $data=json_decode($msg,true);
            $this->transfer_customer($data);
        }
    }

    /**
     * @param $data
     * 分配在职成员的客户
     */
    public function transfer_customer($data)
    {

        if (empty($data['handover_userid'])) {
            return '企业成员userid不能为空';
        }

        $old_staff_user = UserWechatName::query()->where('follow_user_userid', '=', $data['handover_userid'])->first();
        if (empty($old_staff_user)) {
            return '企业成员不存在';
        }

        if (empty($data['takeover_userid'])) {
            return '被分配的企业成员userid不能为空';
        }

        $staff_user = UserWechatName::query()->where('follow_user_userid', '=', $data['takeover_userid'])->first();
        if (empty($staff_user)) {
            return '被分配的企业成员不存在';
        }
        if ($data['handover_userid'] == $data['takeover_userid']) {
            return '被分配的企业成员不能和原来的企业成员相同';
        }

        if (empty($data['userids'])) {
            return '客户不能为空';
        }

        $userids_arr = explode(',', $data['userids']);
        if (empty($userids_arr)) {
            return '客户不能为空';
        }

        $user_list = UserWechat::query()->whereIn('id', $userids_arr)
            ->where('follow_user_userid', $data['handover_userid'])
            ->where('transfer_status', '<>', UserWechat::TRANSFER_STATUS_WAIT)
            ->get(['id', 'external_userid'])->toArray();

        $user_list_count = count($user_list);
        if ($user_list_count != count($userids_arr)) {
            return '客户参数错误';
        }

        if ($user_list_count > 100) {
            return '每次最多分配100个客户';
        }


        DB::beginTransaction();
        $transfer_record_id=$data['transfer_record_id']??'';

        //添加转移记录
        if(empty($transfer_record_id)){

            $UserWechatTransferRecord= new UserWechatTransferRecord();
            $UserWechatTransferRecord->handover_user_id = $old_staff_user->id;
            $UserWechatTransferRecord->takeover_user_id = $staff_user->id;
            $UserWechatTransferRecord->handover_userid = $old_staff_user->follow_user_userid;
            $UserWechatTransferRecord->takeover_userid = $staff_user->follow_user_userid;
            $UserWechatTransferRecord->total = $user_list_count;
            $res=$UserWechatTransferRecord->save();
            if (!$res) {
                DB::rollBack();
                return '操作失败';
            }

            $transfer_record_id=$UserWechatTransferRecord->id;
        }

        //查询是否存在客户转移任务
        $UserWechatTransfer = UserWechatTransfer::query()->where('handover_user_id', $old_staff_user->id)->where('takeover_user_id', $staff_user->id)->first();
        if (!$UserWechatTransfer) {
            $UserWechatTransfer = new UserWechatTransfer();
        }

        $UserWechatTransfer->handover_user_id = $old_staff_user->id;
        $UserWechatTransfer->takeover_user_id = $staff_user->id;
        $UserWechatTransfer->handover_userid = $old_staff_user->follow_user_userid;
        $UserWechatTransfer->takeover_userid = $staff_user->follow_user_userid;
        $UserWechatTransfer->status = UserWechatTransfer::STATUS_WAIT;//等待接替

        $res = $UserWechatTransfer->save();
        if (!$res) {
            DB::rollBack();
            return '操作失败';
        }

        //调用企业微信接口
        $data = [
            'handover_userid' => $data['handover_userid'],
            'takeover_userid' => $data['takeover_userid'],
            'external_userid' => array_column($user_list, 'external_userid'),
            "transfer_success_msg" => "您好，您的服务已升级，后续将由我的同事" . $staff_user->qw_name . "接替我的工作，继续为您服务。"
        ];

        $detail_res = ImClient::curlPost('https://qyapi.weixin.qq.com/cgi-bin/externalcontact/transfer_customer?access_token=' . $this->token, json_encode($data));
        $detail_res = json_decode($detail_res, true);

//        var_dump($detail_res);

        if ($detail_res['errcode'] != 0) {
            DB::rollBack();
            return false;
        }

        $format_user=[];
        foreach ($user_list as $user){
            $format_user[$user['external_userid']]=$user;
        }

        foreach ($detail_res['customer'] as $customer){

            $save_data = [

                'transfer_id' => $UserWechatTransfer->id,
                'transfer_record_id' => $transfer_record_id,

                'user_wechat_id' => $format_user[$customer['external_userid']]['id'],
                'external_userid' => $customer['external_userid'],

                'handover_user_id' => $old_staff_user->id,
                'takeover_user_id' => $staff_user->id,

                'handover_userid' => $old_staff_user->follow_user_userid,
                'takeover_userid' => $staff_user->follow_user_userid,
                'takeover_time' => time(),

                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ];

            if($customer['errcode']==0){
                //修改客户的接替状态为 等待接替
                UserWechat::query()->where('external_userid', $customer['external_userid'])->update(['transfer_status' => UserWechat::TRANSFER_STATUS_WAIT, 'updated_at' => date('Y-m-d H:i:s')]);
            }else{
                //转移失败
                $save_data['status']=-1;
                $save_data['errcode']=$customer['errcode'];
                $save_data['return_info']=json_encode($customer);
            }

            DB::table(UserWechatTransferLog::DB_TABLE)->insert($save_data);

        }

        DB::commit();

        return true;

    }




    /**
     * 查询分配结果
     */
    public function transfer_result()
    {

        //查询需要监测的转移客户任务
        $list = UserWechatTransfer::query()->where('status', UserWechatTransfer::STATUS_WAIT)->get();

        DB::beginTransaction();

        foreach ($list as $transfer) {

            $res = $this->transfer_result_api($transfer,$transfer->next_cursor);
            if (!$res) {
                DB::rollBack();
                return false;
            }
        }

        DB::commit();
        return true;
    }

    public function transfer_result_api(UserWechatTransfer $transfer,$cursor)
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
        $detail_res = json_decode($detail_res, true);

//        var_dump($detail_res);

        $next_cursor = $detail_res['next_cursor'] ?? '';

        if ($detail_res['errcode'] == 0) {

            $is_finished=true;//是否完成转移

            foreach ($detail_res['customer'] as $customer) {


                //如果存在等待转移状态的数据 则表示为完成转移
                if($customer['status'] ==UserWechat::TRANSFER_STATUS_WAIT){
                    $is_finished=false;
                }

                //同步客户转移状态
                $UserWechat = UserWechat::query()->where('transfer_status', UserWechat::TRANSFER_STATUS_WAIT)->where('external_userid', $customer['external_userid'])->first();
                if ($UserWechat) {
                    $UserWechat->transfer_status = $customer['status'];
                    $res=$UserWechat->save();
                    if (!$res) {
                        return false;
                    }
                }

                //转移成功更新客户信息
                if ($customer['status'] == UserWechat::TRANSFER_STATUS_FINISH && $UserWechat) {

                    $this->get_user_info($customer['external_userid']);

                    //修改之前的跟进记录
                    UserWechatFollow::query()->where('external_userid', $customer['external_userid'])->where('status', UserWechatFollow::STATUS_ING)->update([
                        'status' => UserWechatFollow::STATUS_END,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    //添加新的跟进记录
                    $UserWechatFollow = new UserWechatFollow();
                    $UserWechatFollow->user_wechat_id = $UserWechat->id;
                    $UserWechatFollow->follow_user_id = $transfer->takeover_user_id;
                    $UserWechatFollow->external_userid = $customer['external_userid'];
                    $UserWechatFollow->follow_user_userid = $transfer->takeover_userid;
                    $res=$UserWechatFollow->save();
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

                    $res=$UserWechatTransferLog->save();
                    if (!$res) {
                        return false;
                    }

                }
            }


            //如果当前接口的数据 都是已完成转移的数据
            if($is_finished &&  $transfer->next_cursor==$cursor){
                $transfer->next_cursor=$next_cursor;
            }

            //更新转移记录的转移
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
            $record_list=UserWechatTransferRecord::query()->where('status',UserWechatTransferRecord::STATUS_WAIT)->get();
            if($record_list){
                foreach ($record_list as $record){


                    //完成转移的数量
                    $record->finish_total=UserWechatTransferLog::query()->where('transfer_record_id',$record->id)->where('status',UserWechat::TRANSFER_STATUS_FINISH)->count();

                    //等待转移的数量
                    $record->wait_total=UserWechatTransferLog::query()->where('transfer_record_id',$record->id)->where('status',UserWechat::TRANSFER_STATUS_WAIT)->count();

                    //判断是否转移完成
                    $log_count=UserWechatTransferLog::query()->where('transfer_record_id',$record->id)->where('status','<>',UserWechat::TRANSFER_STATUS_WAIT)->count();
                    if($record->total <= $log_count){
                        $record->status=UserWechatTransferRecord::STATUS_FINISH;
                    }

                    $record->save();

                }
            }

        }else{

//            var_dump($detail_res);
            return  false;

        }


        //递归查询下一页
        if ($next_cursor) {
            sleep(1);
            return $this->transfer_result_api($transfer,$next_cursor);
        } else {
            return true;
        }

    }
}
