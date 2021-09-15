<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

/**
 * Description of AddressController
 *
 * @author wangxh
 */
class UserWechat extends Controller {


    public function Index(){

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
                        $us = DB::table('nlsg_user_wechat3')->where("external_userid", $add_v['external_contact']['external_userid'])
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

                        DB::table('nlsg_user_wechat3')->insert($insert_data);
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

}
