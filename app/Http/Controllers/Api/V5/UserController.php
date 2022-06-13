<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Coupon;
use App\Models\History;
use App\Models\LiveUserPrivilege;
use App\Models\MeetingSales;
use App\Models\UserInvite;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Servers\V5\UserServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use JWTAuth;
use App\Models\User;
use App\Models\FeedBack;
use App\Models\UserFollow;
use App\Models\Comment;
use App\Models\Wiki;
use App\Models\VipUser;

class UserController extends Controller
{



    /**
     * @api {post} api/v5/user/user_his_list   获取学习榜单
     * @apiVersion 5.0.0
     * @apiName  user_his_list
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/user_his_list
     *
     * @apiParam {string}   token  当前用户token
     * @apiParam {string}   phone  手机号
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *
     *         ]
     *     }
     *
     */
    public function userHisList(Request $request){

        $uid = $this->user['id']??0;
        $page = $request->input('page');

        $week_day       = getWeekDay();
        $week_one       = $week_day['monday'];
        $top_week_one   = $week_day['top_monday'];

        if($page <= 1){
            $data = User::getUserHisLen(20);
        }else{
            $data = [];
        }
        //自己的排名
        $u_data = [
            "id" => $uid,
            "nickname"  => '',
            "headimg"   => '',
            "his_num"   => 0,
            "his_num_n"      => 0,
            "rank"      => 0,
            "is_like"   => 0,
        ];
        if(!empty($uid)){
            $cache_key_name = 'his_len_deteil_'.$uid.'_'.$top_week_one;
            $u_data = Cache::get($cache_key_name);
            if (empty($u_data)) {
                $user_data = History::select("user_id")->selectRaw('sum(time_number) as num')
                    ->where('created_at','>',$top_week_one)
                    ->where('created_at','<',$week_one)//->where('is_del',0)
                    ->where('user_id',$uid)
                    ->where('time_number','>', 0)
                    ->first()->toArray();

                $sql = 'select count(*) as count from (select  sum(time_number) as num,user_id from nlsg_history where created_at > ? and created_at < ? AND time_number >0  group by user_id HAVING sum(time_number )>=?) as count_table';
                $his_data = DB::select($sql,[$top_week_one,$week_one,$user_data['num']]);

                $u_data['nickname'] = $this->user['nickname']??'';
                $u_data['headimg']  = $this->user['headimg']??'';
                $u_data['his_num_n']  = $user_data['num'] >0 ?$user_data['num']: 0;
                $u_data['his_num']  = SecToTime($user_data['num']);
                $u_data['rank']     = $his_data[0]->count;
                Cache::put($cache_key_name, $u_data, 86400);
            }



            //是否页面自我点赞
            $cache_key_name = 'his_like_'.$uid.'_'.$top_week_one;
            $is_like = Cache::get($cache_key_name);
            if(!empty($is_like)){
                $u_data['is_like'] = 1;
            }


        }

        return success(['rank_data'=>$data,'user'=>$u_data]);
    }

    public function histLike(Request $request){
        $uid = $this->user['id']??0;
        if(!empty($uid)){
            //上周一
            $week_day       = getWeekDay();
            $top_week_one   = $week_day['top_monday'];

            //页面自我点赞
            $cache_key_name = 'his_like_'.$uid.'_'.$top_week_one;
            Cache::put($cache_key_name, 1, 86400*7);
        }


        return success();
    }

    public function settings(Request $request){
        return $this->getRes((new UserServers())->settings($request->input(),$this->user['id']));
    }

}
