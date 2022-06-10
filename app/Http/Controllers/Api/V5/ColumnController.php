<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class ColumnController extends Controller
{



    /**
     * @api {get} /api/v5/column/get_camp_list 训练营(暂时废弃)
     * @apiName v5 get_camp_list
     * @apiVersion 5.0.0
     * @apiGroup five_Column
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
                            ['type','In',[3, 4]],
                            ['id','In',$relation_id],
                            ['is_test','In',$is_test],
        ],$order_str);
        //非我的订阅
        $list = $columnObj->getColumn([
                        ['type','In',[3, 4]],
                        ['is_start','=',0],
                        ['id','NotIn',$relation_id],
                        ['is_test','In',$is_test],
        ],$order_str);
//        dd($list);


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
//            if($v['is_start'] == 0){
//                $new_res['start_list'][] = $v;
//            }else{
//                //  5.0.1 暂时不需要线下课
//                $new_res['list'][] = $v;
//            }
        }
        return $this->success($new_res);
    }





}
