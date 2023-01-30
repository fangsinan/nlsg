<?php

namespace App\Servers\Bliss;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class MessageCourseServer
{
    public $user;
    public $relation_id;
    public $receive_user;
    public $err_msg='';

    public function __get($name)
    {
        $fun_name='get_'.$name;
        if(method_exists($this,$fun_name)){
            return $this->$fun_name();
        }else{
            return null;
        }
    }


    /**
     * todo sgl
     */
    public function get_test(){
        $this->user_nickname='user_nickname';
        $this->signin_time='signin_time';
        $this->course_title='course_title';
        $this->out_class_time='out_class_time';
    }


    /**
     * 获取站内信参数
     */
    public function get_msg_param($msg){

        $msg_name=$msg->name;
        if($msg_name){
            $this->$msg_name;
        }

        $message= $msg->message;
        if  (preg_match_all( "!\{\{(\w+)\}\}!" ,  $message ,  $matches ))
        {
            foreach ($matches[1] as $match){
                $msg_val=$this->$match;

                if($msg_val===null){
                    $this->err_msg=$match.'字段不存在';
                    return false;
                }

                $message=str_replace("{{".$match."}}",$msg_val,$message);
            }
        }
        $msg->message=$message;

        return  $msg;

    }

    /**
     * @param $msg
     * 获取发送的参数
     */
    public  function get_template_param($msg){

        $msg_name=$msg->name;

        if($msg_name){
            $this->$msg_name;
        }

        $this->relation_id=$msg->relation_id;
        $this->receive_user=$msg->receive_user;

        $User=User::query()->where('id',$this->receive_user)->first();
        if(!$User){
            $this->err_msg='用户不存在';
            return false;
        }
        if(empty($User->xfxs_wxopenid)){
            $this->err_msg='用户openid不存在';
            return false;
        }
        $msg->openid=$User->xfxs_wxopenid;

        //获取模板
        $template_param=json_decode($msg->template_param,true);
        $msg->template_id=$template_param['template_id'];
        $template_param['touser']=$msg->openid;

        foreach ($template_param['data'] as $key=>$val){
            if  (preg_match_all( "!\{\{(\w+)\}\}!" ,  $val['value'] ,  $matches ))
            {
                foreach ($matches[1] as $match){
                    $msg_val=$this->$match;

                    if($msg_val===null){
                        $this->err_msg=$match.'字段不存在';
                        return false;
                    }

                    $val['value']=str_replace("{{".$match."}}",$msg_val,$val['value']);
                }
                $template_param['data'][$key]=$val;
            }
        }

        $msg->template_param=json_encode($template_param,JSON_UNESCAPED_UNICODE);

        return  $msg;

    }

}
