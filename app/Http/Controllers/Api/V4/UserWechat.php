<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;
use WXBizMsgCrypt;


/**
 * Description of AddressController
 *
 * @author wangxh
 */
class UserWechat extends Controller {

/*************************   暂时废弃  ***************************************/
    public function Index(){
        dd("end");
        set_time_limit(0);
        $department_ids = ConfigModel::getData(58);
        $department_ids = json_decode($department_ids,true);
//dd($department_ids);
        foreach ($department_ids as $key=>$val){
            self::TestImp($val['id'], $val['cursor']);
        }
        return 1;
    }

    public static function TestImp($id, $cursor=''){

        //获取access_token
        $corpid = "wwb4a68b6963803c46";
        $Secret = "RB7XUdI7hZy8Y7hDgJ0cw5BqeULPZK0FBgvljcrsY8Q";
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$Secret";
        $res = ImClient::curlGet($url);
        $res = json_decode($res,true);
//dd($res);
        $access_token = $res['access_token'];
//        $access_token = "2MYfeOyV9Me5iyqqfbQ2_fgdUHEjMtNwmjVI1aC9UiJqwn3gHIV5z96SGCzRWVIdx4gkYhbFWPnOjvIv4BRja3yrtsKeo9BH_jCh_rp8N1a80POzg8_bDW2-1zY0RODE6OkNaU3J6j7lEGH_mzECV84WPpIBnCWyX-5Es1VYYrv4V8CGMADWVwmzIlJtwYsXX6FNYlpqPQpe6nwM_V7PzA";

        //通过部门id 获取成员id  唐山部门 id为3
        $department_id = $id;
        $department_url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=0";
        $department_res = ImClient::curlGet($department_url);
        $department_res = json_decode($department_res,true);
        //dd($department_res);
        $getList_uids = [];
        if( $department_res['errcode'] == 0 ){
            foreach ($department_res['userlist'] as $key=>$val){
                $getList_uids[] = $val['userid'];
            }
        }
        $re=[];
        $getList_uids = array_chunk($getList_uids, 100);
        //dd($getList_uids);
        //通过组合部门成员id  批量获取客户详情
//dd($getList_uids);
        foreach ($getList_uids as $key=>$val){

            $getList_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/batch/get_by_user?access_token=$access_token";
            //$cursor = $cursor;
            $post_data = [
                'userid_list'   => $val,
                'cursor'        => $cursor,//游标
                'limit'         => 50,
            ];

            $flag = true;
            while ($flag){
                $post_data['cursor'] = $cursor;
                $re = [
                    'id'=>$id,
                    'cursor'=>$post_data['cursor']
                ];

                $getList_res = ImClient::curlPost($getList_url,json_encode($post_data));
                $getList_res = json_decode($getList_res,true);
                //dump($getList_res);
                $insert_data = [];
                if( $getList_res['errcode'] == 0 ){
                    //dd($getList_res['external_contact_list']);
                    $cursor = $getList_res['next_cursor']??'';
                    //处理客户信息入库  一次50条
                    foreach ($getList_res['external_contact_list'] as $add_k=>$add_v){
                        //$add_v['external_contact']['external_userid'] = 'wmk8dJEQAALpKQnSTSIqRMYZezZ482eA';
                        $us = DB::table('nlsg_user_wechat')->where("external_userid", $add_v['external_contact']['external_userid'])
                            ->get()->toArray();

                        if( !empty($us) ){
                            continue;
                        }

                        //  dump($add_v);
                        $add_data['follow_user_userid']     = $add_v['follow_info']['userid'] ??'';
                        $add_data['follow_user_remark']     = $add_v['follow_info']['remark']??'';
                        $add_data['follow_user_description']= $add_v['follow_info']['description']??'';
                        $add_data['follow_user_createtime'] = $add_v['follow_info']['createtime']??'';
                        $add_data['follow_user_tags_add_way']    = $add_v['follow_info']['add_way']??'';
                        $add_data['follow_user_tags_oper_userid']= $add_v['follow_info']['oper_userid']??"";
                        $add_data['external_userid']     = $add_v['external_contact']['external_userid']??"";
                        $add_data['name']     = $add_v['external_contact']['name']??"";
                        $add_data['type']     = $add_v['external_contact']['type']??"";
                        $add_data['avatar']     = $add_v['external_contact']['avatar']??"";
                        $add_data['gender']     = $add_v['external_contact']['gender']??"";
                        $add_data['unionid']     = $add_v['external_contact']['unionid']??"";
                        $insert_data[] = $add_data;
                    }
                    if(!empty($insert_data)){

                        DB::table('nlsg_user_wechat')->insert($insert_data);
                        $insert_data = [];
                    }
                }
                \Log::info('next_cursor-'.$id.'   :'.$getList_res['next_cursor']??'');
                if(empty($getList_res['next_cursor'])){  //游标为空  说明下次无数据  结束此次循环
                    $flag = false;
                }

            }

            dump($re);

        }
    }


    /*************************   暂时废弃  ***************************************/


    public static function AddUserWechat(){

        //set_time_limit(0);
        $department_ids = ConfigModel::getData(58);
        $department_ids = json_decode($department_ids,true);

        foreach ($department_ids as $key=>$val){
            self::UserImport($val['id'], $val['cursor']);
        }
        return 1;
    }

    public static function UserImport($id, $cursor=''){

        $table = 'nlsg_user_wechat';
        //获取access_token
        $corpid = "wwb4a68b6963803c46";
        $Secret = "RB7XUdI7hZy8Y7hDgJ0cw5BqeULPZK0FBgvljcrsY8Q";
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$Secret";
        $res = ImClient::curlGet($url);
        $res = json_decode($res,true);
        $access_token = $res['access_token'];

//        $access_token = "5YizNPCOhzSoWPtm4y0A4iJQcKs-AJFr9lPk_TWdXFNWYnHpot3WLNvPeAm2WyXFe6ezZLHAJisA0zfRe7eewEBIu4I3OzxVzJJd8sl0EFbHc67XvTQ-CB9zhUVXH1XYDRlCvXP_Dr6CkMiWWSBh5qLGTIOcJ7kcVIu0tXxZM2oA-jHKF3QLjKIRED56_qA7Pqy1UksGuvCDVPlSxJIARg";

        //通过部门id 获取成员id  唐山部门 id为3
        $department_id = $id;
        $department_url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=0";
        $department_res = ImClient::curlGet($department_url);
        $department_res = json_decode($department_res,true);
        //dd($department_res);
        $getList_uids = [];
        if( $department_res['errcode'] == 0 ){
            foreach ($department_res['userlist'] as $key=>$val){
                $getList_uids[] = $val['userid'];
            }
        }


        //通过部门成员id  批量获取客户详情
        foreach ($getList_uids as $key=>$val){
            $getUidsList_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/list?access_token=$access_token&userid=$val";
            $getUidList_res = ImClient::curlGet($getUidsList_url);
            $getUidList_res = json_decode($getUidList_res,true);
            $getWechatIds = [];
            if( $getUidList_res['errcode'] == 0 ){
                $users = DB::table($table)
                    ->whereIn("external_userid", $getUidList_res['external_userid'])
                    ->pluck("external_userid")->toArray();

                $getWechatIds = array_diff($getUidList_res['external_userid'], $users);
            }

            //$getWechatIds里包含未入库的数据
            if(empty($getWechatIds)){
                //该用户已导完 跳出本次循环
                continue;
            }
            $getList_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/batch/get_by_user?access_token=$access_token";

            $post_data = [
                'userid_list'   => [$val],
                'cursor'        => $cursor,//游标
                'limit'         => 100,
            ];

            $flag = true;
            while ($flag){
                $post_data['cursor'] = $cursor;
                $getList_res = ImClient::curlPost($getList_url,json_encode($post_data));
                $getList_res = json_decode($getList_res,true);
                $insert_data = [];
                if( $getList_res['errcode'] == 0 ){

                    $cursor = $getList_res['next_cursor']??'';
                    //处理客户信息入库  一次100条
                    foreach ($getList_res['external_contact_list'] as $add_k=>$add_v){
                        if( !in_array($add_v['external_contact']['external_userid'], $getWechatIds) ){
                            continue;
                        }

                        $add_data['follow_user_userid']     = $add_v['follow_info']['userid'] ??'';
                        $add_data['follow_user_remark']     = $add_v['follow_info']['remark']??'';
                        $add_data['follow_user_description']= $add_v['follow_info']['description']??'';
                        $add_data['follow_user_createtime'] = $add_v['follow_info']['createtime']??'';
                        $add_data['follow_user_tags_add_way']    = $add_v['follow_info']['add_way']??'';
                        $add_data['follow_user_tags_oper_userid']= $add_v['follow_info']['oper_userid']??"";
                        $add_data['external_userid']     = $add_v['external_contact']['external_userid']??"";
                        $add_data['name']     = $add_v['external_contact']['name']??"";
                        $add_data['type']     = $add_v['external_contact']['type']??"";
                        $add_data['avatar']     = $add_v['external_contact']['avatar']??"";
                        $add_data['gender']     = $add_v['external_contact']['gender']??"";
                        $add_data['unionid']     = $add_v['external_contact']['unionid']??"";
                        $insert_data[] = $add_data;
                    }
                    //dd($insert_data);
                    if(!empty($insert_data)){
                        DB::table($table)->insert($insert_data);
                        $insert_data = [];
                    }
                }
                if(empty($getList_res['next_cursor'])){  //游标为空  说明下次无数据  结束此次循环
                    $flag = false;
                }
            }

        }
    }



    public static function UserImport1($id, $cursor=''){

        $table = 'nlsg_user_wechat';
        //获取access_token
        $corpid = "wwb4a68b6963803c46";
        $Secret = "RB7XUdI7hZy8Y7hDgJ0cw5BqeULPZK0FBgvljcrsY8Q";
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$Secret";
        $res = ImClient::curlGet($url);
        $res = json_decode($res,true);
        $access_token = $res['access_token'];
        //dump($access_token);
        //$access_token = "84vtAyZoX0ot_kSX5tFVeybXetrRLjub2IKlQj6Ua-mVvEWeFmOn3AD0_0496a109huLjSCAmnf1AuUMhRpJqfmlmpgeBgr2vwuTuvB04GyzwgyKNM3prI6AuMaS2W5VohqbNOkSMjYo7pUqY4Me68zwpJIbl_rp9C0xG_dhrWm2NH2Sjj9tvRE9j-EvStL967kBCy58-uK6841Z7eRtOg";

        //通过部门id 获取成员id  唐山部门 id为3
        $department_id = $id;
        $department_url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=0";
        $department_res = ImClient::curlGet($department_url);
        $department_res = json_decode($department_res,true);
        //dd($department_res);
        $getList_uids = [];
        if( $department_res['errcode'] == 0 ){
            foreach ($department_res['userlist'] as $key=>$val){
                $getList_uids[] = $val['userid'];
            }
        }

        //通过部门成员id  批量获取客户详情
        foreach ($getList_uids as $key=>$val){
            $getUidsList_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/list?access_token=$access_token&userid=$val";
            $getUidList_res = ImClient::curlGet($getUidsList_url);
            $getUidList_res = json_decode($getUidList_res,true);
            $getWechatIds = [];
            if( $getUidList_res['errcode'] == 0 ){
                $users = DB::table($table)
                    ->whereIn("external_userid", $getUidList_res['external_userid'])
                    ->pluck("external_userid")->toArray();

                $getWechatIds = array_diff($getUidList_res['external_userid'], $users);
            }

            //$getWechatIds里包含未入库的数据
            if(empty($getWechatIds)){
                //该用户已导完 跳出本次循环
                continue;
            }

            self::getUserDetail($getWechatIds);

        }
    }



    public static function getUserDetail($getWechatIds = []){

        if( empty($getWechatIds) || !is_array($getWechatIds) ){
            return '';
        }
        $table = 'nlsg_user_wechat';

        $insert_data = [];
        foreach ($getWechatIds as $u_key=>$u_val){
            $getDetail_url = "https://qyapi.weixin.qq.com/cgi-bin/externalcontact/get?access_token=$access_token&external_userid=$u_val";
            $detail_res = ImClient::curlGet($getDetail_url);
            $detail_res = json_decode($detail_res,true);

            if( $detail_res['errcode'] == 0 ){
                $add_data['follow_user_userid']             = $detail_res['follow_user'][0]['userid'] ??'';
                $add_data['follow_user_remark']             = $detail_res['follow_user'][0]['remark']??'';
                $add_data['follow_user_description']        = $detail_res['follow_user'][0]['description']??'';
                $add_data['follow_user_createtime']         = $detail_res['follow_user'][0]['createtime']??'';
                $add_data['follow_user_tags_add_way']       = $detail_res['follow_user'][0]['add_way']??'';
                $add_data['follow_user_tags_oper_userid']   = $detail_res['follow_user'][0]['oper_userid']??"";
                $add_data['external_userid']                = $detail_res['external_contact']['external_userid']??"";
                $add_data['name']                           = $detail_res['external_contact']['name']??"";
                $add_data['type']                           = $detail_res['external_contact']['type']??"";
                $add_data['avatar']                         = $detail_res['external_contact']['avatar']??"";
                $add_data['gender']                         = $detail_res['external_contact']['gender']??"";
                $add_data['unionid']                        = $detail_res['external_contact']['unionid']??"";
                $insert_data[] = $add_data;
            }
        }
        if(!empty($insert_data)){
            DB::table($table)->insert($insert_data);
            $insert_data = [];
        }

    }



    public function Callback(Request $request){
        //dd($this->getInputSource->all());
        $params = $request->input();
//
//
//
////        $params = [
////            "msg_signature"=> "3852e794fbf4dca953444b5a2409ee764eab8421",
////            "timestamp"=>"1631845073",
////            "nonce"=> "1631909869",
////            "echostr"=>"6MJ8dV5OAbEJcL7ZgtxcTXKHiQc7TxMlVKr5zBE8LRUqpShrO4\/HzTZfRTmYFpUGwXljDQRkHJEZt4gmSwxc8Q=="
////
////        ];
//
        $sReqData = file_get_contents("php://input");
        \Log::info('User_Wechat_add-xml:   '.$sReqData);
        \Log::info('User_Wechat_add:   '.json_encode($params));

//        $params = json_decode('{"msg_signature":"6409389f2b81aeb1715466d8a995c9324354af97","timestamp":"1631864159","nonce":"1631623841"}', true);
//        $sReqData = "<xml><ToUserName><![CDATA[wwb4a68b6963803c46]]></ToUserName><Encrypt><![CDATA[v03SOyJReHyxyzFhIx8t3Nak2KY2y9hye4hRievr0I7X0r9fA/4uKEBC5TVnhtbvn+L/NWkEuN7a1KDR1eFntdg8uNx3W79HCJelpmze9si/NOhEMdDBlnd4xYmwWDtN+DnsRkbSjmjNw28fGtQ9CnnLzFNSY8X1md8baxVyRHVY0YzUDnaXCjsZY+eq4ePbdXvgkT1Og1DRohUi7367yumH9wtPgv+YsfSpQA0meAWXcbRtUHAsYWws5H1oRNyeFH6PMZmReAoMMt7DzU+J5KZzonFG6B+pDSAK1+S7buD1OkdTqPzw42VhVGnkB/9K3ha0OE5mdbrXP5bLIZ/XOpB0ORQY5JwKsZAKPaU5dMbWnkYSNL3Swh0BWkyGbKuQo7iKNa6wj8ccmukuFodO2ZxqmdJbvog6dWyRwHI/b1+t1ocxv2gouHS9AB8uZaPptmnsPcFRKQ7OnIAWV08HKFUNBOHSFRVh8zazFCC30PL19yJ4j02CIdLS0ApAUbJFtloyIYI1lxfplMcTNVfOs1w3+RylTwe6GrO9wmoCHIekCbKttFNbRrJiwiuaTyict2Q1gf1kBpukKA04Ox7WWhl/NlYSks0oNF7XN/QRQG6qFVWtWOJZjRG4Ynpi3zn7]]></Encrypt><AgentID><![CDATA[2000003]]></AgentID></xml>";
        $corpId = "wwb4a68b6963803c46";
        $token = "80343WWuvAVpa682";
        $encodingAesKey = "gf5YT3368mO2Qgu1X9ht1x951Q3ItXCZw694S5n4yN6";

        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);

//        $sVerifyMsgSig      = urldecode($params['msg_signature'] ??'');
//        $sVerifyTimeStamp   = urldecode($params['timestamp'] ??'');
//        $sVerifyNonce       = urldecode($params['nonce'] ??'');
//        $sVerifyEchoStr     = urldecode($params['echostr'] ??'');
//        $sEchoStr = "";
//        $errCode = $wxcpt ->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
//
//        if ($errCode == 0) {
//            echo $sEchoStr;
//
//        } else {
//            print("ERR: " . $errCode . "\n\n");
//        }

        $sReqMsgSig     = urldecode($params['msg_signature'] ??'');
        $sReqTimeStamp  = urldecode($params['timestamp'] ??'');
        $sReqNonce      = urldecode($params['nonce'] ??'');



        //解析XML
        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if ($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
            $obj = simplexml_load_string($sMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($obj);
            $user_arr = json_decode($json, true);

            if($user_arr['Event'] == "change_external_contact" && ($user_arr['ChangeType'] == "edit_external_contact" || $user_arr['ChangeType'] == "del_external_contact")){
                //添加好友
                \Log::info('User_Wechat_add_aaa:   '.json_encode($user_arr));

                //$user_arr['ExternalUserID'];//外部联系人id
                //self::getUserDetail([$user_arr['ExternalUserID']]);
            }
            var_dump($sMsg);

        } else {
            print("ERR: " . $errCode . "\n\n");
            //exit(-1);
        }

    }

}
