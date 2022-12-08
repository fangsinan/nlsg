<?php

namespace App\Servers\V5;

use App\Models\User;
use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class XiaoeTechServers
{
    public $err_msg='';
    public $access_token='';
    public function __construct()
    {
        $this->get_token();
    }

    public function get_token(){
        $token_key='xiaoe-tech-token';
        $access_token=Redis::get($token_key);
        if($access_token){
            $this->access_token=$access_token;
            return $access_token;
        }

        $paratms=
            [
            "app_id"=> "appPfbUuN2M8786",
            "client_id"=>  "xopNbM35i9O5609",
            "secret_key"=>  "QS7bKFK2N4SRXTDM0Slcm4D5U5qL1Uo8",
            "grant_type"=>  "client_credential"    //获取token时， grant_type = client_credential
        ];

        $res=self::curlGet('https://api.xiaoe-tech.com/token',$paratms);
        if(empty($res['body']['data']['access_token'])){
            $this->err_msg=$res['body']['msg'];
            return false;
        }

        Redis::setex($token_key, 7000,$res['body']['data']['access_token']);
        $this->access_token= $res['body']['data']['access_token'];
        return $res['body']['data']['access_token'];
    }

    /**
     * @return string
     * (二期)获取小鹅通订单 todo
     */
    public function sync_order_list(){

        if(!$this->access_token){
            return $this->err_msg;
        }

        $paratms=[
            'access_token'=>$this->access_token,
            'page'=>1,
            'page_size'=>1,
        ];

        $res=self::curlPost('https://api.xiaoe-tech.com/xe.ecommerce.order.list/1.0.0',$paratms);
        if($res['body']['code']!=0){
            $this->err_msg=$res['body']['msg'];
            return false;
        }

        $list=$res['body']['data']['list'];

        foreach ($list as $order){

            $order_info=$order['order_info']??[];
            $good_list=$order['good_list']??[];
            $buyer_info=$order['buyer_info']??[];
            $payment_info=$order['payment_info']??[];
            $price_info=$order['price_info']??[];
            $ship_info=$order['ship_info']??[];
            var_dump($order_info);die;
        }

        var_dump($list);die;
    }

    /**
     * 注册新用户
     */
    public function user_register($phone){

        if(!$this->access_token){
            return $this->err_msg;
        }

        //保存客户信息
        $baseUser=User::query()->where('phone',$phone)->first();
        if(!$baseUser){
            $baseUser=new User();
            $baseUser->phone=$phone;
            $baseUser->nickname= substr_replace($phone,'****',3,4);
            $res=$baseUser->save();
            if(!$res){
                return '用户保存失败';
            }
        }

        $paratms=[
            'access_token'=>$this->access_token,
            'data'=>[
                'phone'=>$phone,
                'avatar'=>config('env.IMAGES_URL').$baseUser->headimg,
                'nickname'=>$baseUser->nickname,
            ],
        ];

        $res=self::curlPost('https://api.xiaoe-tech.com/xe.user.register/1.0.0',$paratms);
        if($res['body']['code']!=0){
            $this->err_msg=$res['body']['msg'];
            return false;
        }

        var_dump($res);die;

    }
    /**
     * 获取推广员列表
     */
    public function sync_user_info(){

        if(!$this->access_token){
            return $this->err_msg;
        }

        $user_id_list=XeUser::query()->where('is_sync',1)->pluck('xe_user_id')->toArray();
        if(empty($user_id_list)){
            return  false;
        }
        $user_id_list_arr=array_chunk($user_id_list,50);

        foreach ($user_id_list_arr as $user_ids){

            $page_index=1;
            $page_size=50;
            $paratms=[
                'user_id_list'=>$user_ids,
                'access_token'=>$this->access_token,
                'page'=>$page_index,
                'page_size'=>$page_size,
            ];

            $res=self::curlPost('https://api.xiaoe-tech.com/xe.user.batch_by_user_id.get/1.0.0',$paratms);
            if($res['body']['code']!=0){
                $this->err_msg=$res['body']['msg'];
                return false;
            }

            $return_list=$res['body']['data']['list']??[];

            foreach ($return_list as $user){
                //保存小鹅通用户
                $XeUser=XeUser::query()->where('xe_user_id',$user['user_id'])->first();
                if($XeUser){
                    $XeUser->avatar=$user['avatar'];
                    $XeUser->phone=$user['bind_phone'];
                    $XeUser->phone_collect=$user['collect_phone'];
                    $XeUser->user_created_at=$user['user_created_at'];
                    $XeUser->nickname=$user['user_nickname'];
                    $XeUser->wx_union_id=$user['wx_union_id'];
                    $XeUser->wx_open_id=$user['wx_open_id'];
                    $XeUser->wx_app_open_id=$user['wx_app_open_id'];
                    $XeUser->save();
                }
            }
            sleep(1);
        };
    }

    /**
     * 获取推广员列表
     */
    public function sync_distributor_list(){

        if(!$this->access_token){
            return $this->err_msg;
        }

        do {

            $redis_page_index_key='xe_get_distributor_list_page_index';
            $page_index=Redis::get($redis_page_index_key)??1;
            $page_size=50;
            $paratms=[
                'access_token'=>$this->access_token,
                'page_index'=>$page_index,
                'page_size'=>$page_size,
            ];

            $res=self::curlPost('https://api.xiaoe-tech.com/xe.distributor.list.get/1.0.0',$paratms);
            if($res['body']['code']!=0){
                $this->err_msg=$res['body']['msg'];
                return false;
            }

            $return_list=$res['body']['data']['return_list']??[];

            if(empty($return_list)){
                Redis::set($redis_page_index_key,1);
                return false;
            }else{
                Redis::set($redis_page_index_key,$page_index+1);
            }

            foreach ($return_list as $distributor){

                //保存小鹅通用户
                $XeUser=XeUser::query()->where('xe_user_id',$distributor['user_id'])->first();
                if(!$XeUser){
                    $XeUser =new XeUser();
                    $XeUser->xe_user_id=$distributor['user_id'];
                    $XeUser->avatar=$distributor['avatar'];
                    $XeUser->nickname=$distributor['nickname'];
                    $XeUser->is_sync=1;
                    $XeUser->save();
                }

                //保存推广员
                $XeDistributor=XeDistributor::query()->where('xe_user_id',$distributor['user_id'])->first();
                if(!$XeDistributor){
                    $XeDistributor =new XeDistributor();
                }

                $XeDistributor->xe_user_id=$distributor['user_id'];
                $XeDistributor->nickname=$distributor['nickname'];
                $XeDistributor->level=$distributor['level'];
                $XeDistributor->group_name=$distributor['group_name'];
                $XeDistributor->group_id=$distributor['group_id'];
                $XeDistributor->avatar=$distributor['avatar'];
                $XeDistributor->save();
            }

            sleep(1);

        } while ($return_list);
    }

    /**
     * 发送get请求
     * @param
     * @return
     */
    public static function curlGet($url, $queryparas = array(), $timeout = 2, $header = array(), $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送post请求
     * @param
     * @return
     */
    public static function curlPost($url, $postdata = array(), $queryparas = array(), $header = array(), $timeout = 2, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (!empty($header)) {
            $header_str = implode('', $header);
            if (strpos($header_str, "application/x-www-form-urlencoded") !== false) {
                $postdata = http_build_query($postdata);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            }
        } else {
            curl_setopt($ch,CURLOPT_HTTPHEADER,["Content-Type:application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送Del请求
     * @param
     * @return
     */
    public static function curlDel($url, $queryparas = array(), $postdata = array(), $header = array(), $timeout = 2, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }
}
