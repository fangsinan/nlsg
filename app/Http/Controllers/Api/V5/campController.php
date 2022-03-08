<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\GetPriceTools;
use App\Models\History;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\WorksInfo;
use Illuminate\Http\Request;

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
     * "data": [
     * {
            id: 519,
            name: "测试创建训练营",
            title: "",
            subtitle: "副标题写啥呢",
            message: "<p><img class="wscnph" src="https://image.nlsgapp.com/nlsg/works/20211202175302856576.png" /><img class="wscnph" src="https://image.nlsgapp.com/nlsg/works/20211202175312662092.png" /></p>",
            column_type: 1,
            user_id: 167204,
            original_price: "10.00",
            price: "0.01",
            online_time: "2021-07-15 00:00:00",
            works_update_time: null,
            index_pic: "nlsg/other/20210602095339524870.jpg",
            cover_pic: "nlsg/other/20210602094843678808.png",
            details_pic: "nlsg/other/20210602095124839952.jpg",
            subscribe_num: 17,
            info_num: 5,
            is_free: 0,
            is_start: 1,
            show_info_num: 3,
            is_sub: 0,
            nickname: "柠檬维c"
     *     }
     * ]
     * }
     */
    public function getCampList(Request $request)
    {

        //排序
        $order_str = $request->input('order') ??"desc";

        $uid = $this->user['id'] ?? 0;
        $columnObj = new Column();
        $subObj = new Subscribe();
        //我的订阅 id
        $relation_id = $subObj->getMySub($uid,7);
        $my_list = $columnObj->getColumn([
                            ['type','=',3],
                            ['id','In',$relation_id],
                        ],$order_str);
        //非我的订阅
        $list = $columnObj->getColumn([
                        ['type','=',3],
                        ['is_start','=',0],
                        ['id','NotIn',$relation_id],
                    ],$order_str);

        $new_res = [
            "my_list"=>$my_list['data'],
            "start_list"=>[],
            "list"=>[],
        ];
        foreach ($list['data'] as $v) {

            $user_info = User::find($v['user_id']);
            //$v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], 7);//训练营订阅
            $v['nickname'] = $user_info['nickname'] ?? '';
            $v['title'] = $user_info['honor'] ?? '';
            $new_res['start_list'][] = $v;
        //    if($v['is_start'] == 0){
        //        $new_res['start_list'][] = $v;
        //    }else{
        //        //  5.0.1 暂时不需要线下课
        //        $new_res['list'][] = $v;
        //    }
        }
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
        $column_id = $request->input('id', 0);
        $activity_tag = $request->input('activity_tag', '');

        $user_id = $this->user['id'] ?? 0;
        if (empty($column_id)) {
            return $this->error(0, 'column_id 不能为空');
        }
        
        $field = ['id', 'name', 'title', 'subtitle', 'type', 'column_type', 'user_id', 'message',
            'original_price', 'price', 'online_time', 'works_update_time', 'index_pic','cover_pic', 'details_pic',
            'is_end', 'subscribe_num', 'info_num', 'is_free', 'category_id', 'collection_num','is_start','show_info_num'];
        $column = Column::getColumnInfo($column_id, $field, $user_id);
        if (empty($column)) {
            return $this->error(0, '内容不存在不能为空');
        }

        //免费试听的章节
        // $free_trial = WorksInfo::select(['id'])->where(['column_id' => $column_id, 'type' => 1, 'status' => 4, 'free_trial' => 1])->first();
        // $column['free_trial_id'] = (string)$free_trial['id'] ?? '';
        //训练营无试听章节
        $column['free_trial_id'] = '';

        $column['twitter_price'] = (string)GetPriceTools::Income(1, 2, 0, 1, $column_id);
        $column['emperor_price'] = (string)GetPriceTools::Income(1, 4, 0, 1, $column_id);
        $column['service_price'] = (string)GetPriceTools::Income(1, 5, 0, 1, $column_id);
        $column['online_time'] = date('Y-m-d',strtotime($column['online_time']));
        

        $user = User::find($column['user_id']);
        $column['title'] = $user['honor'] ?? '';

        return $this->success([
            'list' => $column,
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
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "works_data": {
     * "id": 16,
     * "title": "如何经营幸福婚姻",  //标题
     * "subtitle": "",             //副标题
     * "cover_img": "/nlsg/works/20190822150244797760.png",   //封面
     * "detail_img": "/nlsg/works/20191023183946478177.png",   //详情图
     * "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
     * "view_num": 1295460,     //浏览数
     * "price": "29.90",
     * "subscribe_num": 287,       关注数
     * "is_free": 0,
     * "is_end": 1,
     * "info_num": 2       //现有章节数
     * "history_ount": 2%       //总进度
     * },
     * "info": [
     * {
     * "id": 2,
     * "type": 1,
     * "title": "02坚毅品格的重要性",
     * "section": "第二章",       //章节数
     * "introduce": "第二章",     //章节简介
     * "view_num": 246,        //观看数
     * "duration": "03:47",
     * "free_trial": 0,     //是否可以免费试听
     * "href_url": "",
     * "time_leng": "10",      //观看 百分比
     * "time_number": "5"      //观看 分钟数
     * },
     * {
     * "id": 3,
     * "type": 2,
     * "title": "03培养坚毅品格的方法",
     * "section": "第三章",
     * "introduce": "第三章",
     * "view_num": 106,
     * "duration": "09:09",
     * "free_trial": 0,
     * "href_url": "",
     * "time_leng": "10",
     * "time_number": "5"
     * }
     * ]
     * }
     * }
     */
    public function getLectureList(Request $request)
    {

        $lecture_id = $request->input('lecture_id', 0);
        $order = $request->input('order', 'asc');
        $flag = $request->input('flag', '');
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
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
        $info = $infoObj->getInfo($getInfo_id, $is_sub, $user_id, $getInfo_type, $order, $page_per_page, $page, $size, $column_data,$os_type);
        if($column_data['type'] == 3) {
            //训练营规定展示章节
            $info = array_reverse($info);
        }



        $column_data['is_sub'] = $is_sub;
        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($lecture_id, $history_type, $user_id);  //讲座


        $column_data['history_count'] = 0;
        if ($column_data['info_num'] > 0) {
            $column_data['history_count'] = round($hisCount / $column_data['info_num'] * 100);
        }

        $historyData = History::getHistoryData($lecture_id, $history_type, $user_id);

        return $this->success([
            'lecture_data' => $column_data,
            'info' => $info,
            'historyData' => $historyData
        ]);
    }


}
