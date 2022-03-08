<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\GetPriceTools;
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


}
