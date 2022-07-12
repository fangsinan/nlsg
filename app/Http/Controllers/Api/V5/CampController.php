<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\CampPrize;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnEndShow;
use App\Models\ColumnWeekModel;
use App\Models\ColumnWeekReward;
use App\Models\ContentLike;
use App\Models\History;
use App\Models\OfflineProducts;
use App\Models\Poster;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampController extends Controller
{
    /**
     * @api {get} /api/v5/camp/get_camp_list 训练营list
     * @apiName v5 get_camp_list
     * @apiVersion 5.0.0
     * @apiGroup five_Camp
     *
     * @apiParam {int} page
     * @apiParam {int} order desc 默认倒序 asc 正序
     *
     * @apiSuccess {number} start_list   即将开营
     * @apiSuccess {number} list   训练营
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": []
     * }
     */
    public function getCampList(Request $request)
    {
        //排序
        $order_str = $request->input('order') ??"desc";

        $uid = $this->user['id'] ?? 0;
        $columnObj = new Column();
        $subObj = new Subscribe();

        $is_test=[0];
        if(!empty($this->user['is_test_pay'])){
            $is_test=[0,1];
        }

        //我的订阅 id
        $relation_id = $subObj->getMySub($uid,7);
        $my_list = $columnObj->getColumn([
                            ['id','In',$relation_id],
                            ['type','=',3],  //我的报名只显示期数
                            ['status','=',1],
                            ['is_test','In',$is_test],
                        ],$order_str);
        //非我的订阅 显示父类 不显示订阅训练营所属的父类
        $list = $columnObj->getColumn([
                        ['type','=',4],
                        ['status','=',1],
                        ['is_test','In',$is_test],

            // ['is_starwt','=',0],
                // ['id','NotIn',$relation_id],
                    ],$order_str);

        $new_res = [
            "my_list"=>$my_list['data'],
            "start_list"=>[],
            "offline_list"=>[],
        ];
        foreach ($list['data'] as $v) {

            $user_info = User::find($v['user_id']);
            //$v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], 7);//训练营订阅
            $v['nickname'] = $user_info['nickname'] ?? '';
            $v['title'] = $user_info['honor'] ?? '';
            $new_res['start_list'][] = $v;
        }


        //线下课类型
        $new_res['offline_list'] = OfflineProducts::select(['id','title','subtitle','total_price','price','cover_img','image','video_url', 'off_line_pay_type','is_show','subscribe_num'])
            ->where([ 'type'=>3, 'is_del' => 0])->get()->toArray();

        return $this->success($new_res);
    }




    /**
     * @api {get} /api/v5/camp/get_camp_detail 训练营详情
     * @apiName get_camp_detail
     * @apiVersion 5.0.0
     * @apiGroup five_Camp
     *
     * @apiParam {int} id  训练营id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "column_info": {
     * }
     * }
     * }
     */

    public function getCampDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            // 'info_id' => 'bail:numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(1000,$validator->getMessageBag(),(object)[]);
        }

        $column_id = $request->input('id', 0);
        $activity_tag = $request->input('activity_tag', '');

        $user_id = $this->user['id'] ?? 0;
        if (empty($column_id)) {
            return $this->error(0, 'column_id 不能为空');
        }

        $field = ['id', 'name', 'title', 'subtitle', 'type', 'column_type', 'user_id', 'message',
            'original_price', 'price', 'online_time', 'works_update_time', 'index_pic','cover_pic', 'details_pic',
            'is_end', 'subscribe_num', 'info_num', 'is_free', 'category_id', 'collection_num','is_start','show_info_num'
        ,'comment_num','info_column_id','classify_column_id','can_h5','end_time'];
        $column = Column::getColumnInfo($column_id, $field, $user_id);
        if (empty($column)) {
            return $this->error(0, '内容不存在不能为空');
        }

        //免费试听的章节
        // $free_trial = WorksInfo::select(['id'])->where(['column_id' => $column_id, 'type' => 1, 'status' => 4, 'free_trial' => 1])->first();
        // $column['free_trial_id'] = (string)$free_trial['id'] ?? '';
        //训练营无试听章节
        $column['free_trial_id'] = '';

        // $column['twitter_price'] = (string)GetPriceTools::Income(1, 2, 0, 1, $column_id);
        // $column['emperor_price'] = (string)GetPriceTools::Income(1, 4, 0, 1, $column_id);
        // $column['service_price'] = (string)GetPriceTools::Income(1, 5, 0, 1, $column_id);
        $column['online_time'] = date('Y-m-d',strtotime($column['online_time']));


        $user = User::find($column['user_id']);
        $column['title'] = $user['honor'] ?? '';
        // 训练营奖励和证书 获取
        $Letter = ColumnEndShow::GetShowLetter($column['classify_column_id']);
        $column['letter']   = $Letter['letter'];
        $column['cer_img']      = $Letter['cer_img'];

        // 结营后是否弹学习证书
        $end_start = ColumnEndShow::EndShow($user_id,$column_id);
        // $column['end_show_letter']  = $end_start['is_letter']; //是否拆开信件 结营当天必弹  弹完点击就算拆开信件
        $column['end_show']         = $end_start['is_cer']; // 是否领取奖励   需要结营后 手动点击
        $column['cer_is_show']      = $end_start['cer_is_show']; // 是否有资格显示  条件 1、结营后  2、学完所有周的课程
        $real_user = DB::table("crm_camp_user")->select("real_name")->where(['user_id' => $user_id])->first();
        if(empty($real_user->real_name)){
            $real_name = $this->user['nickname'] ?? "";
        }else{
            $real_name = $real_user->real_name ?? "";
        }
        $column['camp_user_name'] = $real_name;

        // 统一全局type
        $types = FuncType(140);
        $is_sub = Subscribe::isSubscribe($user_id, $column_id, $types['sub_type']);
        $column['poster'] = Poster::where(['type'=>1,'relation_id'=>$column_id])->pluck('image')->toArray();
        if(empty($column['poster'])){ // 如果为空则取用父级
            $f_columnID = !empty($column['classify_column_id']) ?$column['classify_column_id']: $column['info_column_id'];
            $column['poster'] = Poster::where(['type'=>1,'relation_id'=>$f_columnID])->pluck('image');
        }
        $column['is_sub'] = $is_sub;
        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($column_id, $types['his_type'], $user_id);  //讲座

        $column['history_count'] = 0;
        $info_num  = $column['type'] == 3 ? $column['show_info_num']:$column['info_num'];
        if ($info_num > 0) {
            $column['history_count'] = round($hisCount / $info_num * 100);
        }
        
        //历史记录
        $column['historyData'] = History::getHistoryData($column_id, $types['his_type'], $user_id);
        // 是否收藏
        
        $column['is_collection'] = Collection::isCollection([$types['col_type']],$column_id,0,$user_id);
        // 是否父类
        $column['is_parent'] = 0;
        if($column['type'] == 4){
            $column['is_parent'] = 1;
            // $column['online_time'] = Column::getColumnNewStartTime($column['id']);
        }

        // 获取第一章节 info_id
        $column['first_info_id'] = Column::getFirstInfo($column['info_column_id'] ?? $column['id']);
        
        // 时间 
        $column['start_time_str'] = empty($column["online_time"]) ?"0":strtotime($column["online_time"]);
        $column['end_time_str'] = empty($column["end_time"]) ?"0":strtotime($column["end_time"]);
        return $this->success([
            'list' => $column
        ]);
    }


    /**
     * @api {get} /api/v5/camp/get_lecture_list  训练营目录
     * @apiName get_lecture_list
     * @apiVersion 5.0.0
     * @apiGroup five_Camp
     *
     * @apiParam {int} lecture_id  讲座id
     * @apiParam {int} user_id 用户id  默认0
     * @apiParam {int} order asc和 desc  默认asc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     */
    public function getLectureList(Request $request)
    {
        $lecture_id = $request->input('lecture_id', 0);
        $order = $request->input('order', 'asc');
        $flag = $request->input('flag', '');
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $version = $request->input('version')??'5.0.0';
        $order = $order ?? 'asc';

        $page = intval($page) <= 0 ?1:$page;
        $user_id = $this->user['id'] ?? 0;
        if (empty($lecture_id)) {
            return $this->error(0, '参数有误：lecture_id ');
        }
        //IOS 通过审核后修改  并删除返回值works_data
        $column_data = Column::select(['id', 'name', 'name as title','type' , 'title', 'subtitle','index_pic', 'cover_pic as cover_img', 'details_pic as detail_img', 'message','details_pic','cover_pic',
            'view_num', 'price', 'subscribe_num', 'is_free', 'is_end', 'info_num','show_info_num','info_column_id','status'])
        //    ->where(['id' => $lecture_id, 'status' => 1])->first();
            ->where(['id' => $lecture_id,'type'=>3 ])->first();  // 已购中 不需要操作status


        if (empty($column_data)) {
            return $this->error(0, '参数有误：无此信息');
        }
        $type = 7;
        $history_type = 5; //训练营 历史记录type值
        $getInfo_type = 4; //训练营 info type值

        $is_sub = Subscribe::isSubscribe($user_id, $lecture_id, $type);

        //因为需要根据$column_data的type类型校验 sub表  所以需要全部查询后进行上下架状态校验
        //未关注   正常按照上下架 显示数据
        //已关注则不操作
        if($is_sub == 0 && $column_data['type'] == 2 && $column_data['status'] !==1){  //未关注 下架 不显示数据
            return $this->error(0, '产品已下架');
        }

        //1、加字段控制需要查询的章节
        $page_per_page = 50;
        $size = $column_data['show_info_num'];
        $page = $page>1?100:$page;

        $os_type = $request->input('os_type', 0);

        //仅限于训练营  因为多期训练营共用同一章节
        $getInfo_id = $lecture_id;
        if($column_data['info_column_id'] > 0 ){
            $getInfo_id = $column_data['info_column_id'];
        }
        //查询章节、
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($getInfo_id, $is_sub, $user_id, $getInfo_type, $order, $page_per_page, $page, $size, $column_data,$os_type,$version);
        if($column_data['type'] == 3) {
            //训练营规定展示章节
            $info = array_reverse($info);
        }



        // $column_data['is_sub'] = $is_sub;
        //查询总的历史记录进度`
        // $hisCount = History::getHistoryCount($lecture_id, $history_type, $user_id);  //讲座


        // $column_data['history_count'] = 0;
        // if ($column_data['info_num'] > 0) {
        //     $column_data['history_count'] = round($hisCount / $column_data['info_num'] * 100);
        // }

        // $historyData = History::getHistoryData($lecture_id, $history_type, $user_id);

        return $this->success($info);
    }


    /**
     * @api {get} /api/v5/camp/camp_study 训练营学习奖励
     * @apiName camp_study
     * @apiVersion 5.0.0
     * @apiGroup five_Camp
     *
     * @apiParam {int} id  训练营id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "column_info": {
     * }
     * }
     * }
     */
    public function campStudy(Request $request){

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(1000,$validator->getMessageBag()->first(),(object)[]);
        }
        $camp_id = $request->input('id', 0);  //训练营id
        // $camp_info_id = $request->input('info_id', 0);
        $user_id = $this->user['id'] ?? 0;
        // is_show      结营后三天不显示奖励弹窗
        // now_week     获得第几周的奖励 当前学习的章节是第N周 就显示获得第N周的奖励
        // 周奖励状态     status  3已领取，2待领取，1补卡领取，0未开始
        // 奖品信息             

        $column_data = Column::find($camp_id);
        if (empty($column_data)) {
            return $this->error(1000, '参数有误：无此信息',(object)[]);
        }
        
        // 训练营 每周开放六节课程
        // 查询训练营目前开放的全部课程 ，每六个章节为一周，查询历史记录表是否完结
        $is_sub = Subscribe::isSubscribe($user_id, $camp_id, 7);
        if($is_sub ==0){
            return $this->error(1000,'您当前尚未加入该训练营',(object)[]);
        }
        // crm_camp_prize  奖品
        $prize = CampPrize::getPrizeByclassifyId($column_data['classify_column_id']);
        $prize = array_column($prize,null,"id");

        $res = [
            'is_show'   =>0,
            'now_week'  =>"",
            'count_down'=>"",
            'week_day'  =>[],
        ];
        // 结营三天后  不显示弹窗
        // if( $column_data['is_start'] == 2 &&
        //     strtotime("+3 day",strtotime($column_data['end_time'])) <= time() ){
        //     return $this->success($res);
        // }
        
        // 用户学习进度 获取奖励
        $reward = ColumnWeekReward::select('is_get','speed_status','end_time','week_id')->where([
            'user_id'       => $user_id,
            'relation_id'   => $column_data['id'],
        ])->orderBy('week_id')->get()->toArray();
        $reward = array_column($reward,null,'week_id');

        // 对应所有的周
        $weeksData = ColumnWeekModel::select('id','prize_id','start_at','end_at')->where('relation_id',$camp_id)
            ->orderBy("start_at")->get()->ToArray();
        $weeks = array_column($weeksData,null,'id');

        //   首先定位所学的最大周
        //  对所有周进行排序   排序后剔除未学过的周 
        $week_ids = array_column($reward,"week_id");    // 已学周id
        $weeksData = array_column($weeksData,'id');  // 该训练营所有周id
        $weeksDataDiff = array_diff($weeksData,$week_ids);
        $new_reward_id = array_diff($weeksData,$weeksDataDiff);
        $max_id = empty($new_reward_id) ? 0 : end($new_reward_id);   //获取最大周id


        // 将已学周id  按照所有周的顺序排序
        // $new_reward_id = [];
        // $max_reward_id = 0;
        // foreach ($weeksData as $key=>$value){
        //     $kk = array_search($value,$week_ids);
        //     if($kk !== false){
        //         $new_reward_id[] = $value;
        //         $max_reward_id = $value;
        //     }
        // }
        
        // 获取所学最大周的时间
        $start_at = $weeks[$max_id]['start_at'] ??0;

        $is_show = 0;
        $now_week = "";
        $new_reward = [];

        foreach($weeks as $key=>$week_val){
            $prize_id = $week_val['prize_id'];
            $status = 0;
             // 将最大周之前的数据全部置为补卡
            //  只要不是领取状态则都是补卡领取状态
            if( $start_at > $week_val['start_at']){
                    $status = 1;
            }
            
            if(!empty($reward[$key])){

                if($reward[$key]["speed_status"] == 2 && $reward[$key]["is_get"] == 1){
                    $status = 3;
                }else if( $reward[$key]['speed_status'] == 2 && $reward[$key]['is_get'] == 0 ){
                    // 当前周   
                    $status = 2;
                    $now_week = $prize[$prize_id]['period_num_name']??'';
                    $now_week="恭喜您！获得".$now_week."学习奖励";
                    $is_show = 1;
                // }else if( $reward[$key]['speed_status'] == 1 && ['is_get'] == 0 ){
                //     $status = 1;
                // }else if( $reward[$key]['speed_status'] == 0 ){
                //     $status = 0;
                }
            }
            
            $new_reward[] = [
                // 'week_id' => $reward[$key]['week_id'],
                'week_title' => $prize[$prize_id]['period_num_name']??'',
                'status' => $status,
                'prize_title' => $prize[$prize_id]['prize_title']??'',
                'prize_pic' => $prize[$prize_id]['prize_pic']??'',
            ];
        }

        // 结营后
        if( $column_data['is_start'] == 2){
            // 第四天的零点 截止
            // $column_data['end_time'] = "2022-7-3 1:20";
            $end_time = strtotime("+4 day",strtotime($column_data['end_time']));
            if( $end_time <= time() ){
                return $this->success($res);
            }
            // 显示倒计时几天 三天内显示
            $day = intval(($end_time-time())/3600/24);
            if($day > 0 && $day <= 3){
                $now_week = "补卡倒计时 ".$day."天";
                $res['count_down'] = (string)$day;
            } else{
                $res['is_show'] = 0;  // 结营三天后不显示此奖励弹窗
            }
        }

        $res['is_show'] = $is_show;
        $res['now_week']= $now_week;
        $res['week_day']= $new_reward;
        return $this->success($res);
    }

    // /api/v5/camp/camp_study_get  奖励领取操作
    public function campStudyGet(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'id' => 'required|numeric',
            // 'info_id' => 'bail:required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->getMessageBag()->first());
        }
        $column_id = $request->input('id');
        $user_id = $this->user['id'] ?? 0;
        // 发放奖励到课程

        // 查看当前所需要领取的奖励  ( 已经学完 未领取的数据 )
        $week_ids = ColumnWeekReward::where([
            'user_id'       => $user_id,
            'relation_id'   => $column_id,
            'speed_status'   => 2,
            'is_get'   => 0,
        ])->pluck("week_id")->toArray();
        $camp = CampPrize::getPrizeByWeekId($week_ids);
        if(!empty($camp) ){
            // 添加至sub表
            $time = time();
            $starttime = strtotime(date('Y-m-d', $time));
            $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期
            foreach($camp as $sub_val){
                if($sub_val['sub_type'] > 0){
                    // 校验是否存在
                    $sub = Subscribe::where([
                        'relation_id'   => $sub_val['relation_id'],
                        'type'      => $sub_val['sub_type'], 
                        'user_id'   => $user_id,
                        'status'    => 1,
                    ])->first();
                    if(empty($sub)){
                         $subscribes[] = [
                            'relation_id'   => $sub_val['relation_id'],
                            'type'      => $sub_val['sub_type'], 
                            'user_id'   => $user_id,
                            'pay_time'  => date("Y-m-d H:i:s", $time),
                            'start_time'=> date("Y-m-d H:i:s", $starttime),
                            'end_time'  => date("Y-m-d H:i:s", $endtime),
                            'give'      => 30, 
                            'status'    => 1,
                        ];
                    }
                   
                }
            }
            if(!empty($subscribes)){
                Subscribe::insert($subscribes);
            }
            
        }


        // 全部重置为已领取
        ColumnWeekReward::where([
            'user_id'       => $user_id,
            'relation_id'   => $column_id,
            'speed_status'   => 2,
        ])->update([
            'is_get' =>1
        ]);

        return $this->success();
    }



    /**
     * @api {get} /api/v5/camp/camp_end_show 训练营结营弹窗 (拆信和领取证书)
     * @apiName camp_end_show
     * @apiVersion 5.0.0
     * @apiGroup five_Camp
     *
     * @apiParam {int} id  训练营id
     * @apiParam {int} end_type 1 拆信   2领证书
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "column_info": {
     * }
     * }
     * }
     */

    public function campEndShow(Request $request)
    {
        $column_id = $request->input('id', 0);
        $end_type = $request->input('end_type', 0);

        $user_id = $this->user['id'] ?? 0;
        if (empty($column_id) || empty($end_type) ) {
            return $this->error(0, 'column_id or end_type 不能为空');
        }

        // $end_show = ColumnEndShow::where([
        //     'user_id' =>$user_id,
        //     'relation_id' =>$column_id,
        // ])->first();
        $end_show = ColumnEndShow::EndShow($user_id,$column_id);

        switch ($end_type){
            case 1:
                $edit_data = [
                    'user_id'       =>$user_id,
                    'relation_id'   =>$column_id,
                    'is_letter'     =>1,
                ];
                if($end_show['is_letter'] == 0){
                    $edit_data['letter_at']=date("Y-m-d H:i:s",time());
                }
                
                break;
            case 2:
                $edit_data = [
                    'user_id'       =>$user_id,
                    'relation_id'   =>$column_id,
                    'is_cer'=>1,
                ];
                if($end_show['is_cer'] == 0){
                    $edit_data['cer_at']=date("Y-m-d H:i:s",time());
                }
                break;
            default :
            return $this->success();
        }
        if($end_show['id'] ==0){
            ColumnEndShow::Create($edit_data);
        }else{
            ColumnEndShow::where('id',$end_show['id'])->update($edit_data);
        }

        return $this->success();
    }




    /**
     * {get} /api/v5/camp/camp_like  点赞
     *
     * @apiParam {int} relation_id  对应id
     * @apiParam {int} user_id  用户id
     * @apiParam {int} info_id  当前章节

     */
    public function campLike(Request $request)
    {
        // $type = $request->input('type', 0);
        $relation_id = $request->input('relation_id', 0);
        $info_id = $request->input('info_id', 0);
        $user_id = $this->user['id'] ?? 0;

        if (empty($relation_id) || empty($user_id) || empty($info_id)) {
            return $this->error(0, 'relation_id 或者 user_id 、info_id不能为空');
        }

        $like_res = ContentLike::editLike($user_id, $relation_id, 5, $info_id);
        if(empty($like_res)){
            return $this->error(0,'点赞失败');
        }
        return $this->success();
    }




    /**
     * {get} /api/v5/camp/collection  收藏[专栏、课程、商品]
     *
     * @apiParam {int} type  type 1专栏  2课程  3商品  4书单 5百科 6听书 7讲座  8训练营
     * @apiParam {int} target_id  对应id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} info_id 如果是课程 需要传当前章节
     */
    public function Collection(Request $request)
    {
        $input_type = $request->input('type', 0);
        $target_id = $request->input('target_id', 0);
        $info_id = $request->input('info_id', 0);
        $user_id = $this->user['id'] ?? 0;

        if (empty($target_id) || empty($user_id)) {
            return $this->error(0, 'column_id 或者 user_id 不能为空');
        }
        $type = FuncType($input_type)['col_type']??0;
        //  type 1：专栏  2：课程 3 :商品
        if (!in_array($type, [1, 2, 3, 4, 5, 6, 7, 8])) {
            return $this->error(0, 'type类型错误');
        }
        $is_collection = Collection::CollectionData($user_id, $target_id, $type, $info_id);


        return $this->success($is_collection);
    }

}
