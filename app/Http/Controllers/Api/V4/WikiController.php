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
     * @api {get} api/v4/wiki/index  百科首页
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Wiki
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/wiki/index
     * @apiParam  page 分页
     *
     * @apiSuccess {string} name 百科标题
     * @apiSuccess {string} content 百科描述
     * @apiSuccess {string} cover   百科封面
     * @apiSuccess {string} view_num 浏览数
     * @apiSuccess {string} like_num 收藏数
     * @apiSuccess {string} comment_num 评论数
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": [
     * {
     * "id": 1,
     * "category_id": 1,
     * "name": "室内空气污染对孩子的危害",
     * "content": "社会的进步，工业的发展，导致污染越来越严重，触目惊心",
     * "cover": "/wechat/mall/goods/3264_1512448129.jpg",
     * "view_num": 10,
     * "like_num": 2,
     * "comment_num": 5,
     * }
     * ]
     *     }
     *
     */
    public function index(Request $request)
    {
        $model = new Wiki();
        if ($request->get('category_id')) {
            $model = $model->where('category_id', $request->get('category_id'));
        }
        $lists = $model
            ->select(['id','name','content','cover','view_num','like_num','comment_num'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists['data']);
    }


    /**
     * 百科分类
     */
    public function category()
    {
        $lists = WikiCategory::where('status', 1)
            ->select('id', 'name')
            ->orderBy('created_at')
            ->get()
            ->toArray();
        return $this->success($lists);
    }

    /**
     * 百科详情
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $id = $request->input('id');
        $res = Wiki::select('name', 'content', 'cover', 'view_num', 'like_num', 'comment_num')
            ->find($id);
        if ( ! $res) {
            return $this->error(404, '百科不存在');
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
        $lists = Wiki::select('name', 'content', 'cover', 'view_num', 'like_num', 'comment_num')
            ->where('id', '!=', $id)
            ->limit(2)
            ->get();
        return $this->success($lists);
    }

    /**
     * @api {get} api/v4/wiki/like 点赞
     * @apiVersion 4.0.0
     * @apiName  id  百科id
     * @apiGroup Api
     *
     * @apiSuccess {String} token
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
        if ( ! $id) {
            return false;
        }
        $list = Like::where(['relation_id' => $id, 'user_id' => 1, 'type' => 2])->first();
        if ($list) {
            return error(1000, '不要重复操作');
        }

        $res = Like::create([
            'relation_id' => $id,
            'user_id'     => 1,
            'type'        => 2
        ]);
        if ($res) {
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }


}
