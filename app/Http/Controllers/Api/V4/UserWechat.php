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
use Illuminate\Support\Facades\Redis;
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
            $getWechatIds = array_unique($getWechatIds);
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
                        //处理完该uid  删除数组中的uid 防止当前循环中有重复
                        $del_key = array_search($add_v['external_contact']['external_userid'],$getWechatIds);
                        unset($getWechatIds[$del_key]);
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

            self::getUserDetail($getWechatIds,$access_token);

        }
    }


    public static function getAccess_token(){

        $key = 'redis_wechat_user_access_token';
        $token = Redis::get($key);
        if(empty($token)){
            dump(1);
            $corpid = "wwb4a68b6963803c46";
            $Secret = "RB7XUdI7hZy8Y7hDgJ0cw5BqeULPZK0FBgvljcrsY8Q";
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$Secret";
            $res = ImClient::curlGet($url);
            $res = json_decode($res,true);
            $token = $res['access_token'];
            Redis::setex($key, 7200, $token);
        }

        return $token;


    }


    public static function getUserDetail($getWechatIds = [], $access_token){

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



                //user_wechat_name
                $add_name_data = [
                    'follow_user_userid' => $detail_res['follow_user'][0]['userid'] ??'',
                    'qw_name' => $detail_res['external_contact']['name']??"",

                ];
                $insert_name_data[] = $add_name_data;

            }

        }
        //dump($insert_data);
        if(!empty($insert_data)){
            DB::table($table)->insert($insert_data);
            $insert_data = [];
        }

        //dump($insert_data);
        if(!empty($insert_name_data)){
            DB::table("nlsg_user_wechat_name")->insert($insert_name_data);
            $insert_name_data = [];
        }

    }



    public function Callback(Request $request){
        $params = $request->input();

        $sReqData = file_get_contents("php://input");
        \Log::info('User_Wechat_add-xml:   '.$sReqData);
        \Log::info('User_Wechat_add:   '.json_encode($params));

//        $params = json_decode('{"msg_signature":"4ed965679bcdb1f80c2f221113114e3e92a28c03","timestamp":"1631865361","nonce":"1632589261"}', true);
//        $sReqData = "<xml><ToUserName><![CDATA[wwb4a68b6963803c46]]></ToUserName><Encrypt><![CDATA[bLS+ckmhd4xOGrC6fRHu0ZHaLmOkXTq4AVxuSOagxsQjPIzx/QXFxs1rnZHm3XIQMOJzAdpo4mYeo7iXvjD7sQiJoOhTvjbdmGR4GPl1sVEJTvRDIVxcHgUMnl14LWIpJHLjkxgRcItCXDJ+9HPdN81xg+L/qsG2McXoLxByHAxidxth/6Te2TuVIenttCip95WvvoKLptqiu4Q0CBaN54kzk/C/XNiPkTAFjIskPmYKtA6ueoRgRbi76fRBTiSaiRRJSJo6w1YsL4eG3FpjdNrIgGr2ywnrIIvhSkxl7p1FIMRq8XpCWE8RkTzgfVG6dgSPXUkKDCVOyIFrw/kzPcTc9H5NtQ/R5OlaCdyC2QHWoarTq3Glpo4YDy6QqTiifSkQbC5HcRGVFrDpvU3BZd7P7Wr7tFuQOwKWTu0AOgfUM/FwHnxQeSZdNH45GY2vl5+fmHhE/MiQQlZJNXOuheZD3T5xvfWxZGKjlpynSrZyqwn2z2SZ7pM4VpngSJ/rCvPiDRIC+p0udqNZbOEp+U9mgeN1ty5G0epBNFMDUUCZx3pyI4CQgL8DyT5IKrLyDl/1mZHmMk1LeOREdJyFTPc7AE0cn1wE4ikmDevxCm0zkjsiq5rARIxHFaInrWkc]]></Encrypt><AgentID><![CDATA[2000003]]></AgentID></xml> ";
        $corpId = "wwb4a68b6963803c46";
        $token = "80343WWuvAVpa682";
        $encodingAesKey = "gf5YT3368mO2Qgu1X9ht1x951Q3ItXCZw694S5n4yN6";

        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);

        // 校验回调用
        if(!empty($params['echostr'])){
            $sVerifyMsgSig      = urldecode($params['msg_signature'] ??'');
            $sVerifyTimeStamp   = urldecode($params['timestamp'] ??'');
            $sVerifyNonce       = urldecode($params['nonce'] ??'');
            $sVerifyEchoStr     = urldecode($params['echostr'] ??'');
            $sEchoStr = "";
            $errCode = $wxcpt ->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

            if ($errCode == 0) {
                echo $sEchoStr;

            } else {
                print("ERR: " . $errCode . "\n\n");
            }

            return ;
        }



        $sReqMsgSig     = urldecode($params['msg_signature'] ??'');
        $sReqTimeStamp  = urldecode($params['timestamp'] ??'');
        $sReqNonce      = urldecode($params['nonce'] ??'');


        if(empty($sReqData)){
            return; 
        }
        //解析XML
        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if ($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
            $obj = simplexml_load_string($sMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($obj);
            $user_arr = json_decode($json, true);

            if($user_arr['Event'] == "change_external_contact" && ($user_arr['ChangeType'] == "add_external_contact")){

                //添加好友
                \Log::info('User_Wechat_add_aaa:   '.json_encode($user_arr));
                $users = DB::table('nlsg_user_wechat')
                    ->where("external_userid", $user_arr['ExternalUserID'])
                    ->get()->toArray();
                if(empty($users)){
                    $access_token = self::getAccess_token();
                    //$user_arr['ExternalUserID'];//外部联系人id
                    self::getUserDetail([$user_arr['ExternalUserID']],$access_token);

                }

            }
            dump($sMsg);

        } else {
            print("ERR: " . $errCode . "\n\n");
            //exit(-1);
        }

    }



    public static function UserWechatEdit(){


        //获取access_token
        $access_token = self::getAccess_token();
        //        $access_token = "2MYfeOyV9Me5iyqqfbQ2_fgdUHEjMtNwmjVI1aC9UiJqwn3gHIV5z96SGCzRWVIdx4gkYhbFWPnOjvIv4BRja3yrtsKeo9BH_jCh_rp8N1a80POzg8_bDW2-1zY0RODE6OkNaU3J6j7lEGH_mzECV84WPpIBnCWyX-5Es1VYYrv4V8CGMADWVwmzIlJtwYsXX6FNYlpqPQpe6nwM_V7PzA";

        //通过部门id 获取成员id  唐山部门 id为3
        $department_id = 3;
        $department_url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=0";
        $department_res = ImClient::curlGet($department_url);
        $department_res = json_decode($department_res,true);

        $getList_uids = [];
        $user_ids = DB::table('nlsg_user_wechat_name')->pluck("follow_user_userid")->toArray();
        if( $department_res['errcode'] == 0 ){
            foreach ($department_res['userlist'] as $key=>$val){
                $getList_uids[] = $val['userid'];
            }
            //计算出差集  补充到表中
            $new_user = array_diff($getList_uids,$user_ids);

            $add_data = [];
            foreach ($department_res['userlist'] as $key=>$val){
                if( in_array($val['userid'],$new_user)){
                    $data = [
                        'qw_name' => $val['name'],
                        'follow_user_userid' => $val['userid'],

                    ];
                    $add_data[] = $data;
                }
            }
            if( !empty($add_data) ){
                DB::table('nlsg_user_wechat_name')->insert($add_data);
            }

        }


        return 1;
    }



}
