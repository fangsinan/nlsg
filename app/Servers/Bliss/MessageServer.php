<?php

namespace App\Servers\Bliss;

use App\Models\Bliss\MessageModel;
use App\Models\Bliss\MessageUserModel;
use App\Models\Bliss\MessageWechatModel;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class MessageServer
{

    /**
     * WechatServers::GetToken();
     * 获取token校验信息
     * access_token是公众号的全局唯一接口调用凭据
     * access_token的存储至少要保留512个字符空间。access_token的有效期目前为2个小时  刷新时公众平台后台会保证在5分钟内，新老access_token都可用
     * 此token和获取用户信息token不是同一个，此token用于调用其他接口如分享接口等   用户信息token用于处理支付
     * @return mixed
     */
    //未使用
    public static function GetToken()
    {
        $cache_key_name='nlsg_bliss_wechat_access_token';

        $res = Redis::get($cache_key_name);

        if($res){
            return $res;
        }

        try {

            $app_id = config('env.XFXS_WECHAT_APP_ID');
            $app_secret =  config('env.XFXS_WECHAT_SECRET');

            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $app_id . '&secret=' . $app_secret;

            $rst = curlPost($url);

            if ($rst) {

                $rstJson = json_decode($rst);

                if (isset($rstJson->access_token)) {

                    //写入redis
                    Redis::setex($cache_key_name,7200,$rstJson->access_token);//设置redis缓存

                    return $rstJson->access_token;
                } else {
                    return false;
                }
            }

        } catch (\Exception $e) {
            return false;
        }
    }



    /**
     * 发送消息
     */
    public static function send_msg($uid,$name,$relation_id){

        //获取消息模板
        $MessageModel=MessageModel::query()->where('name',$name)->first();
        if(!$MessageModel){
            return false;
        }

        //站内信
        if($MessageModel->message){
            $MessageUserModel = new MessageUserModel();
            $MessageUserModel->receive_user=$uid;
            $MessageUserModel->relation_id=$relation_id;
            $MessageUserModel->type=$MessageModel->type;
            $MessageUserModel->message_id=$MessageModel->id;
            $MessageUserModel->message=$MessageModel->message;
            $MessageUserModel->save();
        }

        //微信模板消息
        if($MessageModel->wechat){

            $MessageWechatModel = new MessageWechatModel();
            $MessageWechatModel->message_id=$MessageModel->id;
            $MessageWechatModel->type=$MessageModel->type;
            $MessageWechatModel->receive_user=$uid;
            $MessageWechatModel->relation_id=$relation_id;
            $MessageWechatModel->template_param=$MessageModel->wechat;
            $MessageWechatModel->save();
        }

        return true;
    }

    /**
     * 定时发送站内信
     * todo
     */
    public static function msg_send(){

        $msg_list = MessageUserModel::query()
            ->where('status', 0)
            ->limit(100)->get();

        foreach ($msg_list as $msg) {
            $WechatCourseServer = new MessageCourseServer();
            $res = $WechatCourseServer->get_msg_param($msg);
            if (!$res) {

                $msg->remark = $WechatCourseServer->err_msg;
                $msg->status = -1;//系统错误发布失败
                $msg->save();

            } else {

                $msg->status = 1;
                $msg->save();
            }
        }
    }

    /**
     * 定时发送微信模板消息
     * todo
     */
    public static function wechat_msg_send()
    {

        $msg_list = MessageWechatModel::query()
            ->where('status', 0)
            ->limit(100)->get();

        foreach ($msg_list as $msg) {

            $WechatCourseServer = new MessageCourseServer();
            $res = $WechatCourseServer->get_template_param($msg);

            if (!$res) {

                $msg->remark = $WechatCourseServer->err_msg;
                $msg->status = -1;//系统错误发布失败
                $msg->save();

            } else {

                $Access_Token = self::GetToken(); //获取token
                $Url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $Access_Token;
                $content = curlPost($Url, $msg->template_param);
                $Rst = json_decode($content, true);

                $msg->result = $content;
                $msg->status = $Rst['errcode'];
                if($msg->status==0){
                    $msg->status=200;
                }

                $msg->save();
            }
        }
    }

    /**
     * 消息列表
     */
    public static  function msg_search_list($user_id,$data=[]){

        $query=MessageUserModel::query()
            ->where('receive_user',$user_id)
            ->orderBy('id','desc');

        $list=$query->paginate(get_page_size($data));
        return $list;
    }


}
