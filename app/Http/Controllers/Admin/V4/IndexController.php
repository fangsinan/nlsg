<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\Lists;
use App\Models\ListsWork;
use App\Models\Live;
use App\Models\LiveInfo;
use App\Models\MallGoods;
use App\Models\Recommend;
use App\Models\Wiki;
use App\Models\Works;
use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;

class IndexController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/index/works 精选课程
     * @apiVersion 4.0.0
     * @apiName  index/works
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/works
     * @apiDescription 精选课程
     *
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} cover_img 封面图
     * @apiSuccess {string} price    价格
     * @apiSuccess {string}  price  价格
     * @apiSuccess {number}  status  状态
     * @apiSuccess {string}  created_at  创建时间
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function works()
    {
        $lists = Recommend::with('works:id,title,cover_img,price')
            ->select('id', 'relation_id', 'sort', 'created_at', 'status')
            ->where('position', 1)
            ->where('type', 2)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->orderBy('sort', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/rank  首页-排行榜
     * @apiVersion 4.0.0
     * @apiName  index/rank
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/rank
     *
     * @apiParam {string}   type  4 课程 9 百科 10商品
     * @apiSuccess {string}  state 状态 1上架 下架
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id  作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function rank(Request $request)
    {
        $type = $request->get('type') ?? 8;
        if ($type == 8) {
            $lists = ListsWork::with('works:id,title,cover_img,price')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 8)
                ->orderBy('sort')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } elseif ($type == 9) {
            $lists = ListsWork::with('wiki:id,name,cover')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 9)
                ->orderBy('sort')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } elseif ($type == 10) {
            $lists = ListsWork::with('goods:id,name,picture,price')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 10)
                ->orderBy('sort')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }

        return success($lists);
    }

    /**
     * @api {get} api/v4/index/lists  首页-书单推荐
     * @apiVersion 4.0.0
     * @apiName  index/lists
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/lists
     *
     * @apiSuccess {string}  title    标题
     * @apiSuccess {string}  subtitle 副标题
     * @apiSuccess {string}  cover    封面
     * @apiSuccess {string}  num      数量
     * @apiSuccess {string}  status   状态  1上架 2下架
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function lists()
    {
        $lists = Lists::select('id', 'title', 'subtitle', 'cover', 'num', 'status')
            ->where('type', 3)
            ->get()
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/v4/list/works  书单的作品列表
     * @apiVersion 4.0.0
     * @apiName   list/works
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/list/works
     *
     * @apiParam {string}    list_id  书单id
     * @apiSuccess {string}  state 状态 1上架 下架
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id  作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function getListWorks(Request $request)
    {
        $list_id = $request->get('list_id');
        $lists = ListsWork::select('id', 'lists_id', 'works_id', 'state', 'sort', 'type')
            ->where('lists_id', $list_id)
            ->orderBy('sort', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as $kk => &$vv) {
                if ($vv['type'] == 2) {
                    $listen = Works::select(['id', 'title', 'cover_img'])
                        ->where('id', $vv['works_id'])
                        ->where('is_audio_book', 1)
                        ->where('status', 4)
                        ->first();
                    $lists[$kk]['works'] = $listen;
                } elseif ($vv['type'] == 4) {
                    $column = Column::select(['id', 'name', 'cover_pic'])
                        ->where('id', $vv['works_id'])
                        ->where('type', 2)
                        ->where('status', 1)
                        ->first();
                    $lists[$kk]['works'] = $column;
                }
            }

        }
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/goods  首页-推荐商品
     * @apiVersion 4.0.0
     * @apiName   index/goods
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/goods
     *
     * @apiSuccess {string}  sort         排序
     * @apiSuccess {string}  status       状态
     * @apiSuccess {string}  goods        商品
     * @apiSuccess {string}  goods.name   商品名称
     * @apiSuccess {string}  goods.price  价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function goods()
    {
        $lists = Recommend::with('goods:id,name,picture,price,original_price')
            ->select('id', 'relation_id', 'sort', 'created_at', 'status')
            ->where('position', 1)
            ->where('type', 8)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->orderBy('sort', 'desc')
            ->get();

        return success($lists);
    }

    /**
     * @api {get} api/v4/index/wiki  首页-推荐百科
     * @apiVersion 4.0.0
     * @apiName   index/wiki
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/wiki
     *
     * @apiSuccess {string}  sort         排序
     * @apiSuccess {string}  status       状态
     * @apiSuccess {string}  goods        商品
     * @apiSuccess {string}  goods.name   商品名称
     * @apiSuccess {string}  goods.price  价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function wiki()
    {
        $lists = Recommend::with('wiki:id,name,cover,view_num,like_num')
            ->select('id', 'relation_id', 'sort', 'created_at', 'status')
            ->where('position', 1)
            ->where('type', 5)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->orderBy('sort', 'desc')
            ->get();

        return success($lists);
    }

    /**
     * @api {get} api/v4/index/course  首页-推荐课程集合【教育宝典】
     * @apiVersion 4.0.0
     * @apiName  index/course
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/course
     *
     * @apiSuccess {string}  state 状态 1上架 下架
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id  作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function course()
    {
        $lists = ListsWork::with('works:id,title,cover_img,price')
            ->select('id', 'lists_id', 'works_id', 'state')
            ->where('lists_id', 4)
            ->orderBy('sort', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return success($lists);
    }

    /**
     * @api {get} api/v4/index/live   推荐直播
     * @apiVersion 4.0.0
     * @apiName   index/live
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/live
     *
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function live()
    {
        $lists = Recommend::whereHas('live', function($q){
                        $q->select('id','title')->where('status', '=', 4);
                   })
                   ->with('live:id,title')
                   ->select('id','relation_id','created_at')
                   ->where('type', 7)
                   ->where('position', 1)
                   ->where('app_project_type','=',APP_PROJECT_TYPE)
                   ->paginate(10)
                   ->toArray();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/index/add-works 增加/更新推荐课程
     * @apiVersion 4.0.0
     * @apiName  add-works
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-works
     * @apiDescription 增加/编辑推荐课程
     *
     * @apiParam {string} work_id 作品id
     * @apiParam {string} sort 位置
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function addWorks(Request $request)
    {
        $input = $request->all();
        if ( ! empty($input['id'])) {
            Recommend::where('id', $input['id'])->update([
                'relation_id' => $input['work_id'],
                'sort'        => $input['sort'],
                'status'      => $input['status'] ?? 2
            ]);
        } else {
            Recommend::create([
                'relation_id' => $input['work_id'],
                'position'    => 1,
                'type'        => 2,
                'sort'        => $input['sort'] ?? 99,
                'status'      => $input['status'] ?? 2
            ]);
        }

        return success();

    }

    /**
     * @api {post} api/admin_v4/index/add-lists 增加/更新推荐书单
     * @apiVersion 4.0.0
     * @apiName  add-lists
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-lists
     * @apiDescription 增加/编辑推荐书单
     *
     * @apiParam {string} title 标题
     * @apiParam {string} subtitle 副标题
     * @apiParam {string} status   1上架  2下架
     * @apiParam {string} cover   封面
     * @apiParam {string} details_pic   详情图
     * @apiParam {string} sort   位置
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function addLists(Request $request)
    {
        $input = $request->all();
        $sort = $input['sort'] ?? 99;
        $cover = ! empty($input['cover']) ? covert_img($input['cover']) : '';
        $details_pic = ! empty($input['details_pic']) ? covert_img($input['details_pic']) : '';
        if ( ! empty($input['id'])) {
            Lists::where('id', $input['id'])->update([
                'title'       => $input['title'],
                'subtitle'    => $input['subtitle'],
                'status'      => $input['status'] ?? 2,
                'cover'       => $cover,
                'sort'        => $sort,
                'details_pic' => $details_pic
            ]);
        } else {
            Lists::create([
                'title'       => $input['title'],
                'subtitle'    => $input['subtitle'],
                'status'      => $input['status'] ?? 2,
                'type'        => 3,
                'sort'        => $sort,
                'cover'       => $cover,
                'details_pic' => $details_pic
            ]);
        }

        return success();

    }

    /**
     * @api {post} api/admin_v4/index/add-listwork 增加/更新推荐作品
     * @apiVersion 4.0.0
     * @apiName  add-listwork
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-listwork
     * @apiDescription 增加/编辑推荐课程
     *
     * @apiParam {string} lists_id 书单id
     * @apiParam {string} works_id 作品id
     * @apiParam {string} sort 位置
     * @apiParam {string} state 状态 1 上架 2下架
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */

    public function addListWork(Request $request)
    {
        $input = $request->all();
        if ( ! empty($input['id'])) {
            $list = ListsWork::where('id', $input['id'])->first();
            ListsWork::where('id', $input['id'])->update([
                'works_id' => $input['works_id'],
                'sort'     => $input['sort'] ?? 99,
                'state'    => $input['state'] ?? 2,
                'type'     => $input['type'] ?? 0
            ]);
            if ($input['state'] == 2) {
                Lists::where('id', $list->lists_id)->decrement('num');
            }
        } else {
//            $res = ListsWork::where('lists_id', $input['lists_id'])
//                ->where('works_id', $input['works_id'])
//                ->first();
//            if ($res) {
//                return error(1000, '不能添加重复数据');
//            }
            ListsWork::create([
                'lists_id' => $input['lists_id'],
                'works_id' => $input['works_id'],
                'sort'     => $input['sort'] ?? 99,
                'state'    => $input['state'] ?? 2,
                'type'     => $input['type'] ?? 0

            ]);
            Lists::where('id', $input['lists_id'])->increment('num');
        }
        return success();

    }

    /**
     * @api {post} api/admin_v4/index/add-goods 增加/更新推荐商品
     * @apiVersion 4.0.0
     * @apiName  index/add-goods
     * @apiGroup  后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-goods
     * @apiDescription 增加/编辑推荐好物
     *
     * @apiParam {string} goods_id 商品id
     * @apiParam {string} sort 位置
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function addGoods(Request $request)
    {
        $input = $request->all();
        if ( ! empty($input['id'])) {
            Recommend::where('id', $input['id'])->update([
                'relation_id' => $input['goods_id'],
                'sort'        => $input['sort'],
                'status'      => $input['status'] ?? 2
            ]);
        } else {
            Recommend::create([
                'relation_id' => $input['goods_id'],
                'position'    => 1,
                'type'        => 8,
                'sort'        => $input['sort'] ?? 99,
                'status'      => $input['status'] ?? 2
            ]);
        }
        return success();

    }

    /**
     * @api {post} api/admin_v4/index/get-goods 选择商品
     * @apiVersion 4.0.0
     * @apiName  add-goods
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-goods
     * @apiDescription 选择商品
     *
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getMallGoods()
    {
        $lists = MallGoods::where('status', 2)
            ->select('id', 'name')
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/index/get-works 选择作品
     * @apiVersion 4.0.0
     * @apiName  get-works
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-works
     * @apiDescription 选择作品
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getWorks()
    {
        $lists = Works::where('status', 4)
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/index/add-wiki 增加/更新推荐百科
     * @apiVersion 4.0.0
     * @apiName  add-wiki
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-wiki
     * @apiDescription 增加/编辑推荐百科
     *
     * @apiParam {string} wiki_id 百科id
     * @apiParam {string} sort 位置
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function addWiki(Request $request)
    {
        $input = $request->all();
        if ( ! empty($input['id'])) {
            Recommend::where('id', $input['id'])->update([
                'relation_id' => $input['wiki_id'],
                'sort'        => $input['sort'],
                'status'      => $input['status'] ?? 2
            ]);
        } else {
            Recommend::create([
                'relation_id' => $input['wiki_id'],
                'position'    => 1,
                'type'        => 5,
                'sort'        => $input['sort'] ?? 99,
                'status'      => $input['status'] ?? 2
            ]);
        }

        return success();
    }

    /**
     * @api {post} api/admin_v4/index/edit-works 编辑推荐课程
     * @apiVersion 4.0.0
     * @apiName  edit-works
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-works
     * @apiDescription 编辑推荐百科
     *
     * @apiParam {string}  id  推荐id
     *
     * @apiSuccess {string}  relation_id  作品id
     * @apiSuccess {string}  sort   位置
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function editWorks(Request $request)
    {
        $id = $request->get('id');
        $list = Recommend::where('id', $id)
            ->select('id', 'relation_id', 'sort', 'status')
            ->first();
        return success($list);
    }

    /**
     * @api {post} api/admin_v4/index/edit-lists 编辑推荐书单
     * @apiVersion 4.0.0
     * @apiName  edit-lists
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-lists
     * @apiDescription 编辑推荐百科
     *
     * @apiParam {string}  id  书单id
     *
     * @apiSuccess {string}  title  标题
     * @apiSuccess {string}  subtitle   副标题
     * @apiSuccess {string}  status   状态
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function editLists(Request $request)
    {
        $id = $request->get('id');
        $list = Lists::where('id', $id)
            ->select('id', 'title', 'subtitle', 'status', 'cover', 'details_pic', 'sort')
            ->first();
        return success($list);
    }

    /**
     * @api {post} api/admin_v4/index/edit-list-work 编辑书单作品
     * @apiVersion 4.0.0
     * @apiName  edit-list-work
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-list-work
     * @apiDescription 编辑书单作品
     *
     * @apiParam {string}  id  作品id
     *
     * @apiSuccess {string}  lists_id  书单id
     * @apiSuccess {string}  works_id  副标题
     * @apiSuccess {string}  sort      排序
     * @apiSuccess {string}  state     状态
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function editListWork(Request $request)
    {
        $id = $request->get('id');
        $list = ListsWork::where('id', $id)
            ->select('id', 'lists_id', 'works_id', 'sort', 'state')
            ->first();
        return success($list);
    }

    /**
     * @api {get} api/admin_v4/index/get-wiki 选择百科
     * @apiVersion 4.0.0
     * @apiName  get-wiki
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-wiki
     * @apiDescription 选择百科
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getWiki()
    {
        $lists = Wiki::where('status', 1)
            ->select('id', 'name')
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/index/get-lives 选择直播
     * @apiVersion 4.0.0
     * @apiName  get-lives
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-lives
     * @apiDescription 选择直播
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getLives()
    {
        $lists = Live::where('status', 4)
            ->where('is_del', 0)
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    public function getListen()
    {
        $works = Works::select('id', 'title')
            ->where('is_audio_book', 1)
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->get();
        return success($works);
    }

    public function getLecture()
    {
        $lists = Column::select('id', 'name')
            ->where('type', 2)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/index/get-rank-works 选择榜单作品
     * @apiVersion 4.0.0
     * @apiName  get-rank-works
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-rank-works
     * @apiDescription 选择作品
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getRankWorks()
    {
        $lists = Works::where('status', 4)
            ->where('type', 2)
            ->select('id', 'title')
            ->orderBy('created_at', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/index/add-lives 增加/更新推荐直播
     * @apiVersion 4.0.0
     * @apiName  add-lives
     * @apiGroup 后台-首页推荐
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-lives
     * @apiDescription 增加/编辑推荐直播
     *
     * @apiParam {string} live_id 直播id
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function addLive(Request $request)
    {
        $input = $request->all();
        $res = Recommend::where('relation_id', $input['live_id'])
                ->where('position', 1)
                ->where('type', 7)
                ->first();
        if ($res){
            return  error(1004, '不能重复推荐');
        }
        Recommend::create([
            'relation_id' => $input['live_id'],
            'position'    => 1,
            'type'        => 7,
            'sort'        => $input['sort'] ?? 99
        ]);

//        Cache::forget('index_recommend_7_1');

        return success();
    }


    public function delLive(Request $request)
    {
        $id = $request->get('id');
        $res = Recommend::where('id', $id)->delete();
        if ($res){
//            Cache::forget('index_recommend_7_1');
            return success();
        }
        return error(1004, '删除失败');
    }


}



