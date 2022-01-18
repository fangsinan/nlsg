<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\MallGoods;
use App\Models\Wiki;
use App\Models\Works;
use Illuminate\Http\Request;
use App\Models\Lists;
use App\Models\ListsWork;

class RankController extends Controller
{
    /**
     * @api {get} api/v5/rank/works  排行榜-热门课程
     * @apiVersion 5.0.0
     * @apiName  v5 works
     * @apiGroup Rank
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/rank/works
     *
     * @apiSuccess {string} works 课程
     * @apiSuccess {string} works.title    标题
     * @apiSuccess {string} works.subtitle 副标题
     * @apiSuccess {string} works.cover_img 封面
     * @apiSuccess {number} works.chapter_num 章节数
     * @apiSuccess {string} works.subscibe_num 学习人数
     * @apiSuccess {number} works.is_free 是否免费
     * @apiSuccess {number} works.price 课程价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *      "data": [
     * {
     * "id": 8,
     * "title": "热门课程榜单",
     * "works": [
     * {
     * "works_id": 30,
     * "user_id": 168934,
     * "title": "不要羞辱你的孩子 他的心很脆弱",
     * "subtitle": "家庭教育",
     * "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
     * "chapter_num": 8,
     * "subscribe_num": 0,
     * "is_free": 1,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 30
     * }
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * },
     * {
     * "works_id": 31,
     * "user_id": 168934,
     * "title": "小孩子做噩梦怎么办？九成父母都没当回事",
     * "subtitle": "家庭教育",
     * "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
     * "chapter_num": 5,
     * "subscribe_num": 0,
     * "is_free": 1,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 31
     * }
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * },
     * {
     * "works_id": 32,
     * "user_id": 1,
     * "title": "时间就像你手中的冰淇淋",
     * "subtitle": "",
     * "cover_img": "/wechat/works/video/161627/2017061416424169642.jpg",
     * "chapter_num": 0,
     * "subscribe_num": 0,
     * "is_free": 0,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 32
     * }
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * }
     * ]
     *     }
     *
     */
    public function works(Request $request)
    {
        $lists_id = $request->get('lists_id') ?? 0;

        $lists = Lists::select('id', 'title', 'num', 'cover')
            ->where('id', $lists_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ( ! $lists) {
            return error(0,'还没有数据');
        }
        $works_id = ListsWork::where('lists_id', $lists->id)
            ->where('state', 1)
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->pluck('works_id')
            ->toArray();
        $works = Works::with('user:id,nickname,headimg,teacher_title')
            ->whereIn('id', $works_id)
            ->whereIn('type', [2,3])
            ->select('id', 'user_id', 'title', 'subtitle', 'cover_img', 'chapter_num', 'subscribe_num', 'is_free',
                'price')
            ->orderByRaw('FIELD(id,'.implode(',', $works_id).')')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($works);
    }

}
