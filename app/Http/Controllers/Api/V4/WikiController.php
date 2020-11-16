<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Http\Request;
use App\Models\Wiki;
use App\Models\WikiCategory;
use App\Models\Collection;

class WikiController extends Controller
{
    /**
     * @api {get} api/v4/wiki/index  百科-首页
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Wiki
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/wiki/index
     * @apiParam  page        分页
     * @apiParam  category_id 分类id
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
            ->select(['id', 'name', 'content', 'cover', 'view_num', 'like_num', 'comment_num'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists['data']);
    }


    /**
     * @api {get} api/v4/wiki/category  百科-分类
     * @apiVersion 4.0.0
     * @apiName  category
     * @apiGroup Wiki
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/wiki/category
     *
     * @apiSuccess {string} name 分类名
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": [
     * {
     * "id": 1,
     * "name": "两性关系"
     * },
     * {
     * "id": 2,
     * "name": "婚姻哲学"
     * }
     * ]
     *     }
     *
     */
    public function category()
    {
        $lists = WikiCategory::where('status', 1)
            ->select('id', 'name')
            ->orderBy('created_at')
            ->get()
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/v4/wiki/show  百科-详情
     * @apiVersion 4.0.0
     * @apiName  banner
     * @apiGroup Index
     * @apiParam {number} id 百科id
     *
     * @apiSuccess {string} name 标题
     * @apiSuccess {string} content 内容
     * @apiSuccess {string} cover 封面
     * @apiSuccess {string} view_num   浏览数
     * @apiSuccess {string} like_num    收藏数
     * @apiSuccess {string} comment_num 评论数
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *      "data": {
     * "name": "室内空气污染对孩子的危害",
     * "content": "社会的进步，工业的发展，导致污染越来越严重，触目惊心",
     * "cover": "/wechat/mall/goods/3264_1512448129.jpg",
     * "view_num": 10,
     * "like_num": 2,
     * "comment_num": 5
     * }
     *     }
     *
     */
    public function show(Request $request)
    {
        $id = $request->input('id');

        $res = Wiki::select('id','name', 'content', 'cover', 'view_num', 'like_num', 'comment_num')
                ->with([
                    'reward' => function($query){
                        $query->select('id','user_id','relation_id')
                            ->where(['type'=>5, 'reward_type'=>4, 'status'=>1])
                            ->groupBy('user_id');
                    },
                    'reward.user:id,nickname,headimg'
                ])
                ->find($id);
        if ( ! $res) {
            return error(1000, '百科不存在');
        }
        
        Wiki::where('id', $id)->increment('view_num');
        $list    = Collection::where(['type' => 5, 'user_id' => $this->user['id'],'relation_id'=>$id])->first();
        $res->is_collection = $list ? 1 : 0;
        return success($res);
    }

    /**
     * @api {get} api/v4/wiki/related  百科-相关推荐
     * @apiVersion 4.0.0
     * @apiName  related
     * @apiGroup Wiki
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/wiki/related
     * @apiParam {number} id 百科id
     *
     * @apiSuccess {string} name 百科名称
     * @apiSuccess {string} content 百科内容
     * @apiSuccess {string} cover   百科封面
     * @apiSuccess {string} view_num 浏览量
     * @apiSuccess {string} like_num    收藏数
     * @apiSuccess {string} comment_num 评论数
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *     "data": [
     * {
     * "name": "世界名著必读岁月经典",
     * "content": "每个时代都有极其红极广受好评",
     * "cover": "/wechat/mall/mall/goods/389_1519697199.png",
     * "view_num": 5,
     * "like_num": 6,
     * "comment_num": 5
     * }
     * ]
     *     }
     *
     */
    public function related(Request $request)
    {
        $id = $request->input('id');
        $lists = Wiki::select('name', 'content', 'cover', 'view_num', 'like_num', 'comment_num')
            ->where('id', '!=', $id)
            ->limit(2)
            ->get();
        return success($lists);
    }

   

}
