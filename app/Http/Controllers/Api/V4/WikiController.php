<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;
use App\Models\Wiki;
use App\Models\WikiCategory;

class WikiController extends Controller
{
    /**
     * 百科首页
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $model = new Wiki();
        if ($request->get('category_id')){
            $model = $model->where('category_id', $request->get('category_id'));
        }
        $lists = $model
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return  $this->success($lists);
    }


    /**
     * 百科分类
     */
    public function  category()
    {
        $lists = WikiCategory::where('status', 1)
            ->select('id','name')
            ->orderBy('created_at')
            ->get()
            ->toArray();
        return  $this->success($lists);
    }

    /**
     * 百科详情
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function  show(Request $request)
    {
        $id = $request->input('id');
        $res = Wiki::select('name','content','cover','view_num','like_num','comment_num')
                ->find($id);
        if (!$res){
            return $this->error(404,'百科不存在');
        }
        return $this->success($res);
    }

    /**
     * 百科相关推荐
     * @param  Request  $request
     */
    public function related(Request $request)
    {
        $id = $request->input('id');
        $lists = Wiki::select('name','content','cover','view_num','like_num','comment_num')
            ->where('id', '!=', $id)
            ->limit(2)
            ->get();
        return  $this->success($lists);
    }

    /**
     * @api {get} api/v4/wiki/like 点赞
     * @apiVersion 4.0
     * @apiName  id  百科id
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
        $id = $request->input('id');
        if (!$id){
            return false;
        }
        $list = Like::where(['relation_id'=> $id, 'user_id'=> 1, 'type'=>2])->first();
        if ($list){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'relation_id' => $id,
            'user_id'     => 1,
            'type'        => 2
        ]);
        if ($res){
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }


}
