<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{

    /**
     * @api {get} api/v4/like 点赞
     * @apiVersion 4.0
     * @apiName  id  评论id
     * @apiName  type  类型 1.想法 2.百科
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function like(Request $request)
    {
        $id   = $request->input('id');
        $type = $request->input('type');
        if (!$id){
            return false;
        }
        $list = Like::where(['relation_id'=> $id, 'user_id'=> 1, 'type'=>$type])->first();
        if ($list){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'relation_id' => $id,
            'user_id'     => 1,
            'type'        => 1
        ]);
        if ($res){
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }

    /**
     * @api {get} api/unlike 点赞
     * @apiVersion 4.0
     * @apiName  id    评论id
     * @apiName  type  类型 1.想法 2.百科
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function unlike(Request $request)
    {
        $id   = $request->input('id');
        $type = $request->input('type');
        if (!$id){
            return false;
        }
        $res = Like::where(['relation_id'=> $id, 'user_id'=>1, 'type'=>$type])->delete();
        if($res){
            return success('操作成功');
        }
        return error('操作失败');

    }
}
