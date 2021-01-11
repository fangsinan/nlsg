<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\Lists;
use App\Models\Wiki;
use App\Models\WikiCategory;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use App\Models\WorksInfoContent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassController extends ControllerBackend
{


    /**
     * @api {get} api/admin_v4/class/column 专栏列表
     * @apiVersion 4.0.0
     * @apiName  column
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/column
     * @apiDescription 专栏列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status 上下架
     * @apiParam {string} author 作者名称
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     *
     * @apiSuccess {string} name  专栏名称
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} user    作者相关
     * @apiSuccess {string} info_num 作品数量
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
    public function column(Request $request)
    {
        $title = $request->get('title');
        $status = $request->get('status');
        $nickname = $request->get('author');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Column::with('user:id,nickname,phone')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('name', 'like', '%' . $title . '%');
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'user_id', 'name', 'title', 'subtitle', 'price', 'status', 'created_at', 'info_num')
            ->where('type', 1)
            ->where('status', '<>', 3)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/class/lecture 座列表
     * @apiVersion 4.0.0
     * @apiName  lecture
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/lecture
     * @apiDescription 讲座列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status 上下架
     * @apiParam {string} author 作者名称
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     *
     * @apiSuccess {string} name  专栏名称
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} user    作者相关
     * @apiSuccess {string}  info_num 作品数量
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
    public function lecture(Request $request)
    {
        $title = $request->get('title');
        $status = $request->get('status');
        $nickname = $request->get('author');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Column::with('user:id,nickname')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('name', 'like', '%' . $title . '%');
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'user_id', 'name', 'title', 'subtitle', 'price', 'status', 'created_at', 'info_num')
            ->where('type', 2)
            ->where('status', '<>', 3)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/class/works 作品列表
     * @apiVersion 4.0.0
     * @apiName  works
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/works
     * @apiDescription 作品列表
     *
     * @apiParam {number} page 分页
     * @apiParam {number} work_id 编号
     * @apiParam {number} is_pay  是否精品课 1 是 0 否
     * @apiParam {string} title 标题
     * @apiParam {number} status 上下架
     * @apiParam {string} author 作者名称
     * @apiParam {number} category_id 分类id
     * @apiParam {number} type  类型 1 视频 2音频 3 文章
     * @apiParam {string} author 作者名称
     * @apiParam {string} start 开始时间
     * @apiParam {string} end  结束时间
     *
     * @apiSuccess {array} category  分类
     * @apiSuccess {string} title    标题
     * @apiSuccess {array}  user     作者
     * @apiSuccess {number} chapter_num 章节数
     * @apiSuccess {number} price    价格
     * @apiSuccess {number} is_end   是否完结
     * @apiSuccess {number} is_pay   是否精品课 1 是 0 否
     * @apiSuccess {number} status   0 删除 1 待审核 2 拒绝  3通过 4上架 5下架
     * @apiSuccess {string} created_at  创建时间
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

    public function works(Request $request)
    {
        $work_id = $request->get('work_id');
        $title = $request->get('title');
        $status = $request->get('status');
        $type = $request->get('type');
        $nickname = $request->get('author');
        $category_id = $request->get('category_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $is_end = $request->get('is_end');
        $is_pay = $request->get('is_pay');

        $query = Works::with('user:id,nickname')
            ->when($work_id, function ($query) use ($work_id) {
                $query->where('id', $work_id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($is_pay), function ($query) use ($is_pay) {
                $query->where('is_pay', $is_pay);
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($is_end, function ($query) use ($title) {
                $query->where('is_end', $is_end);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($category_id, function ($query) use ($category_id) {
                $query->whereHas('categoryRelation', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'title', 'type', 'is_end', 'created_at', 'user_id', 'view_num', 'status', 'price', 'is_end', 'chapter_num', 'is_pay')
            ->where('status', '>', 0)
            ->where('is_audio_book', 0)
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $category_ids = WorksCategoryRelation::where('work_id', $v['id'])->pluck('category_id');
                $v['category'] = WorksCategory::whereIn('id', $category_ids)->pluck('name');
            }
        }

        return success($lists);

    }

    /**
     * @api {get} api/admin_v4/class/listen 听书
     * @apiVersion 4.0.0
     * @apiName  listen
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/listen
     * @apiDescription 听书
     *
     * @apiParam {number} page 分页
     * @apiParam {number} work_id 编号
     * @apiParam {string} title 标题
     * @apiParam {number} status 上下架
     * @apiParam {string} author 作者名称
     * @apiParam {number} category_id 分类id
     * @apiParam {string} author 作者名称
     * @apiParam {string} start 开始时间
     * @apiParam {string} end  结束时间
     *
     * @apiSuccess {array} category  分类
     * @apiSuccess {string} title    标题
     * @apiSuccess {array}  user     作者
     * @apiSuccess {number} chapter_num 章节数
     * @apiSuccess {number} price    价格
     * @apiSuccess {number} is_end   是否完结
     * @apiSuccess {number} status   0 删除 1 待审核 2 拒绝  3通过 4上架 5下架
     * @apiSuccess {string} created_at  创建时间
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

    public function listen(Request $request)
    {
        $work_id = $request->get('work_id');
        $title = $request->get('title');
        $status = $request->get('status');
        $nickname = $request->get('author');
        $category_id = $request->get('category_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $is_end = $request->get('is_end');

        $query = Works::with('user:id,nickname')
            ->when($work_id, function ($query) use ($work_id) {
                $query->where('id', $work_id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($is_end, function ($query) use ($title) {
                $query->where('is_end', $is_end);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($category_id, function ($query) use ($category_id) {
                $query->whereHas('categoryRelation', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'title', 'type', 'is_end', 'created_at', 'user_id', 'view_num', 'status', 'price', 'is_end', 'chapter_num')
            ->where('is_audio_book', 1)
            ->where('status', '>', 0)
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $category_ids = WorksCategoryRelation::where('work_id', $v['id'])->pluck('category_id');
                $v['category'] = WorksCategory::whereIn('id', $category_ids)->pluck('name');
            }
        }

        return success($lists);

    }

    /**
     * @api {get} api/admin_v4/class/wiki 百科列表
     * @apiVersion 4.0.0
     * @apiName  wiki
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/wiki
     * @apiDescription 百科列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status 上下架
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} cover    作者相关
     * @apiSuccess {string}  info_num 作品数量
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
    public function wiki(Request $request)
    {
        $title = $request->get('title');
        $status = $request->get('status');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Wiki::when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('name', 'like', '%' . $title . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id','category_id','name','cover', 'detail_img', 'status', 'created_at', 'view_num')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $name = WikiCategory::where('id', $v['category_id'])->value('name');
                $v['category_name'] = $name;
            }
        }
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/wiki/add 创建/编辑百科
     * @apiVersion 4.0.0
     * @apiName  add-column
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/wiki/add
     * @apiDescription 创建专栏
     *
     * @apiParam {string} name   标题
     * @apiParam {string} category_id 分类id
     * @apiParam {string} intro    简介
     * @apiParam {string} content  内容
     * @apiParam {string} cover   封面图片
     * @apiParam {string} detail_img 详情图片
     * @apiParam {number} status 状态 1上架  2下架
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
        $name  = $input['name'] ?? '';
        if (!$name) {
            return error('名称不能为空');
        }
        $intro   = $input['intro'] ?? '';
        $content = $input['content'] ?? '';
        $status  = $input['status'] ?? 2;
        $sort    = $input['sort'] ?? 99;
        $category_id   = $input['category_id'] ?? 0;
        $cover = !empty($input['cover']) ? covert_img($input['cover']) : '';
        $detail_img = !empty($input['detail_img']) ? covert_img($input['detail_img']) : '';

        $data = [
            'name'    => $name,
            'intro'   => $intro,
            'content' => $content,
            'status'  => $status,
            'sort'    => $sort,
            'category_id' => $category_id,
            'cover'       => $cover,
            'detail_img' => $detail_img
        ];

        if (!empty($input['id'])) {
            Wiki::where('id', $input['id'])->update($data);
        } else {
            Wiki::create($data);
        }

        return success();

    }

    /**
     * @api {post} api/admin_v4/wiki/category 百科分类
     * @apiVersion 4.0.0
     * @apiName  wiki/category
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/wiki/category
     * @apiDescription 百科分类
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
    public function getWikiCategory()
    {
        $lists = WikiCategory::select('id','name','sort')
                ->where('status', 1)
                ->orderBy('sort', 'desc')
                ->get();
        return success($lists);
    }

    public  function  editWiki(Request $request)
    {
        $id = $request->get('id');
        $list = Wiki::where('id', $id)->first();
        return success($list);
    }

    /**
     * @api {post} api/admin_v4/class/add-column 创建专栏
     * @apiVersion 4.0.0
     * @apiName  add-column
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-column
     * @apiDescription 创建专栏
     *
     * @apiParam {string} name 专栏名称
     * @apiParam {string} subtitle 副标题
     * @apiParam {string} cover_pic 封面图片
     * @apiParam {string} details_pic 详情图片
     * @apiParam {string} message 推荐语
     * @apiParam {number} user_id 作者
     * @apiParam {string} author 作者名称
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
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

    public function addColumn(Request $request)
    {
        $input = $request->all();
        $name = $input['name'] ?? '';
        if (!$name) {
            return error('名称不能为空');
        }
        $cover_pic = !empty($input['cover_pic']) ? covert_img($input['cover_pic']) : '';
        $details_pic = !empty($input['details_pic']) ? covert_img($input['details_pic']) : '';
        $subtitle = $input['subtitle'] ?? '';
        $message = $input['message'] ?? '';
        $user_id = $input['user_id'] ?? 0;
        $original_price = $input['original_price'] ?? 0;
        $price = $input['price'] ?? 0;
        $status = $input['status'] ?? 2;
        $online_type = $input['online_type'] ?? 1;

        $data = [
            'cover_pic' => $cover_pic,
            'details_pic' => $details_pic,
            'name' => $name ?? '',
            'subtitle' => $subtitle,
            'message' => $message,
            'user_id' => $user_id,
            'price' => $price,
            'original_price' => $original_price,
            'type' => 1,
            'status' => $status
        ];

        if (!empty($input['id'])) {
            Column::where('id', $input['id'])->update($data);
        } else {
            Column::create($data);
        }

        return success();

    }

    /**
     * @api {post} api/admin_v4/class/add-lecture 创建讲座
     * @apiVersion 4.0.0
     * @apiName  add-lecture
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-lecture
     * @apiDescription 创建讲座
     *
     * @apiParam {string} name 专栏名称
     * @apiParam {string} subtitle 副标题
     * @apiParam {string} cover_pic 封面图片
     * @apiParam {string} details_pic 详情图片
     * @apiParam {string} message 推荐语
     * @apiParam {number} user_id 作者
     * @apiParam {string} author 作者名称
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
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

    public function addLecture(Request $request)
    {
        $input = $request->all();
        $name = $input['name'] ?? '';
        if (!$name) {
            return error('名称不能为空');
        }
        $cover_pic = !empty($input['cover_pic']) ? covert_img($input['cover_pic']) : '';
        $details_pic = !empty($input['details_pic']) ? covert_img($input['details_pic']) : '';
        $subtitle = $input['subtitle'] ?? '';
        $message = $input['message'] ?? '';
        $user_id = $input['user_id'] ?? 0;
        $original_price = $input['original_price'] ?? 0;
        $price = $input['price'] ?? 0;
        $status = $input['status'] ?? 2;
        $timing_online = $input['online_type'] ?? 1;

        $data = [
            'cover_pic' => $cover_pic,
            'details_pic' => $details_pic,
            'name' => $name,
            'subtitle' => $subtitle,
            'message' => $message,
            'user_id' => $user_id,
            'price' => $price,
            'timing_online'  => $timing_online,
            'original_price' => $original_price,
            'type' => 2,
            'status' => $status
        ];
        //是否自动上架
       if ($timing_online==1){
           $data['online_time'] = $input['timing_time'];
           $data['timing_time'] = $input['timing_time'];
       } else {
           if ($status==4){
               $data['online_time'] =  date('Y-m-d H:i:s', time());
           }
       }
        if (!empty($input['id'])) {
            Column::where('id', $input['id'])->update($data);
        } else {
            $lecture = Column::where('name', $name)->first();
            if ($lecture){
               return error(1000,'不能添加重复数据');
            }
            Column::create($data);
        }

        return success();
    }

    /**
     * @api {post} api/admin_v4/class/add-works 创建精品课
     * @apiVersion 4.0.0
     * @apiName  add-works
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-works
     * @apiDescription 创建精品课
     *
     * @apiParam {string} title 标题
     * @apiParam {string} subtitle 副标题
     * @apiParam {string} user_id 作者
     * @apiParam {string} category_id    分类id
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
     * @apiParam {string} is_end 是否完结
     * @apiParam {number} timing_online 是否自动上架
     * @apiParam {string} status 上架状态
     * @apiParam {string} type  类型 1 视频 2音频 3 文章
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
        $title = $input['title'] ?? '';
        if (!$title) {
            return error('标题不能为空');
        }
        $cover_img = !empty($input['cover_img']) ? covert_img($input['cover_img']) : '';
        $detail_img = !empty($input['detail_img']) ? covert_img($input['detail_img']) : '';
        $user_id = $input['user_id'] ?? 0;
        $category_id = $input['category_id'] ?? 0;
        $original_price = $input['original_price'] ?? 0;
        $price = $input['price'] ?? 0;
        $is_end = $input['is_end'] ?? 0;
        $status = $input['status'] ?? 5;  //0 删除 1 待审 2 拒绝  3通过 4上架 5下架
        $timing_online = $input['online_type'] ?? 0; //是否自动上架  1自动 0手动
        $content = $input['content'] ?? '';
        $is_pay = $input['is_pay'] ?? 0;
        $type   = $input['type'] ?? 1;
        $subtitle   = $input['subtitle'] ?? '';
        $des      = $input['des'] ?? '';


        $data = [
            'title' => $title,
            'subtitle' => $subtitle,
            'des'      => $des,
            'cover_img' => $cover_img,
            'detail_img' => $detail_img,
            'user_id' => $user_id,
            'original_price' => $original_price,
            'timing_online'  => $timing_online,
            'price' => $price,
            'is_end' => $is_end,
            'status' => $status,
            'content' => $content,
            'is_pay' => $is_pay,
            'is_free' => $price == 0 ? 1 : 0,
            'type'   => $type
        ];
        //是否自动上架
       if ($timing_online==1){
           $data['online_time'] = $input['timing_time'];
           $data['timing_time'] = $input['timing_time'];
       } else {
           if ($status==4){
               $data['online_time'] =  date('Y-m-d H:i:s', time());
           }
       }

        if (!empty($input['id'])) {
            Works::where('id', $input['id'])->update($data);
            //增加分类
            WorksCategoryRelation::where('work_id', $input['id'])
                ->delete();
            $id = $input['id'];
        } else {
            $res = Works::where('title', $title)->first();
            if ($res){
                return error(1000, '不能添加重复数据');
            }
            $work = Works::create($data);
            $id = $work ? $work->id : 0;
        }
        WorksCategoryRelation::create([
            'work_id' => $id,
            'category_id' => $input['category_id'] ?? 0
        ]);

        return success();
    }

    /**
     * 获取作者
     */
    public function getColumnAuthors()
    {
        $column = new Column();
        $lists = $column->getColumnUser();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/class/get-work-list 作品详情
     * @apiVersion 4.0.0
     * @apiName  get-work-list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-work-list
     * @apiDescription  作品详情
     *
     * @apiParam {string} title 标题
     * @apiParam {string} cover_img  封面图片
     * @apiParam {string} detail_img 详细图片
     * @apiParam {string} content    内容
     * @apiParam {string} user_id    作者id
     * @apiParam {string} price 售价
     * @apiParam {string} original_price 原价
     * @apiParam {string} status 上架状态
     * @apiParam {string} is_end 是否完结
     * @apiParam {string} view_num 浏览数
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "dat": {
     *
     *    }
     * }
     */
    public function getWorkList(Request $request)
    {
        $id = $request->get('id');
        $work = Works::with('userName:id,nickname')
            ->select('id', 'title', 'subtitle', 'des','cover_img', 'detail_img', 'content', 'status', 'user_id', 'is_end', 'view_num',
                'price', 'original_price', 'is_pay', 'message', 'timing_online','timing_time')
            ->where('id', $id)
            ->first();
        if ($work) {
            $category = WorksCategoryRelation::select('work_id', 'category_id')
                ->where('work_id', $id)
                ->first();
            $work->category_id = $category ? $category->category_id : 0;
        }
        return success($work);
    }

    /**
     * @api {post} api/admin_v4/class/add-works-chapter 增加/编辑章节
     * @apiVersion 4.0.0
     * @apiName  add-works-chapter
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-works-chapter
     * @apiDescription 增加/编辑章节
     *
     * @apiParam {number} id   章节id  存在为编辑
     * @apiParam {string} title 标题
     * @apiParam {string} section 第几节
     * @apiParam {string} introduce 简介
     * @apiParam {string} url    音视频url
     * @apiParam {string} status 状态   0 删除 1 未审核 2 拒绝  3通过 4上架 5下架
     * @apiParam {number} video_id 视频id
     * @apiParam {string} free_trial 是否免费 0 否 1 是
     * @apiParam {string} timing_online  是否自动上线 0 否 1是
     * @apiParam {string} timing_time  自动上线时间
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
    public function addWorkChapter(Request $request)
    {
        $input = $request->all();
        $work_id = $request->get('pid');
        $work = Works::where('id', $work_id)->first();
        if (!$work) {
            return error(1000, '作品不存在');
        }

        $content = $input['content'] ?? ''; //文稿
        $timing_online = $input['online_type'] ?? 0; //是否自动上架  1自动 0手动

        $data = [
            'pid' => $work_id,
            'rank' => $input['rank'] ?? 99,
            'type' => $input['type'] ?? 0,
            'title' => $input['title'] ?? '',
            'section' => $input['section'] ?? '',
            'introduce' => $input['introduce'] ?? '',
            'url' => $input['url'] ?? '',
            'status' => $input['status'] ?? 5,
            'video_id' => $input['video_id'] ?? '',
            'free_trial' => $input['free_trial'] ?? 0,
            'timing_online' => $timing_online ?? 0
        ];

        //是否自动上架
        if ($timing_online==1){
            $data['online_time'] = $input['timing_time'];
            $data['timing_time'] = $input['timing_time'];
        } else {
           if ($input['status'] ==4){
               $data['online_time'] =  date('Y-m-d H:i:s', time());
           }
        }

        if (!empty($input['id'])) {
            WorksInfo::where('id', $input['id'])->update($data);

            //作品章节数量
            if ($input['status'] !=4){
                Works::where('id', $work_id)->decrement('chapter_num');
            } else {
                Works::where('id', $work_id)->increment('chapter_num');
            }

            $list = WorksInfoContent::where('works_info_id', $input['id'])->first();
            if ($list) {
                WorksInfoContent::where('works_info_id', $input['id'])
                    ->update(['content' => $content]);
            } else {
                WorksInfoContent::create([
                    'works_info_id' => $input['id'],
                    'content' => $content
                ]);
            }
        } else {
            $info = WorksInfo::where('title', $input['title'])->first();
            if ($info){
                return error(1000,'不能添加重复数据');
            }

            $res = WorksInfo::create($data);
            if ($res) {
                if ($input['status'] ==4){
                    Works::where('id', $work_id)->increment('chapter_num');
                }

                WorksInfoContent::create([
                    'works_info_id' => $res->id,
                    'content' => $content
                ]);
            }
        }
        return success();

    }

    /**
     * @api {post} api/admin_v4/class/add-listen 创建/编辑听书
     * @apiVersion 4.0.0
     * @apiName  add-listen
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-listen
     * @apiDescription 创建/编辑听书
     *
     * @apiParam {number} id   听书id  id存在为编辑
     * @apiParam {string} title 标题
     * @apiParam {string} user_id 作者
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
     * @apiParam {string} is_end 是否完结
     * @apiParam {number} timing_online 是否自动上架
     * @apiParam {string} status 上架状态
     * @apiParam {string} content 简介
     * @apiParam {string} message 推荐语
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

    public function addListen(Request $request)
    {
        $input = $request->all();
        $title = $input['title'] ?? '';
        if (!$title) {
            return error('标题不能为空');
        }
        $cover_img = covert_img($input['cover_img']) ?? '';
        $user_id = $input['user_id'] ?? 0;
        $category_id = $input['category_id'] ?? 0;
        $original_price = $input['original_price'] ?? 0;
        $is_end = $input['is_end'] ? 1 : 0;
        $status = $input['status'] ?? 5;  //0 删除 1 待审 2 拒绝  3通过 4上架 5下架
        $timing_online = $input['online_type'] ?? 0; //是否自动上架  1自动 0手动
        $content  = $input['content'] ?? '';
        $subtitle = $input['subtitle'] ?? '';

        $data = [
            'title' => $title,
            'subtitle'  => $subtitle,
            'cover_img' => $cover_img,
            'user_id' => $user_id,
            'original_price' => $original_price,
            'is_end' => $is_end,
            'status' => $status,
            'timing_online' => $timing_online,
            'content' => $content,
            'is_audio_book' => 1,
            'type'    => 2
        ];

        //是否自动上架
        if ($timing_online==1){
            $data['online_time'] = $input['timing_time'];
            $data['timing_time'] = $input['timing_time'];
        } else {
           if ($status==4){
              $data['online_time'] =  date('Y-m-d H:i:s', time());
           }
        }

        if (!empty($input['id'])) {
             Works::where('id', $input['id'])->update($data);
             //增加分类
             WorksCategoryRelation::where('work_id', $input['id'])
                 ->delete();
             $id = $input['id'];
         } else {
             $res = Works::where('title', $title)->first();
             if ($res){
                 return error(1000, '不能添加重复数据');
             }
             $work = Works::create($data);
             $id = $work ? $work->id : 0;
         }
         WorksCategoryRelation::create([
             'work_id' => $id,
             'category_id' => $input['category_id'] ?? 0
         ]);

        return success();
    }

    /**
     * @api {post} api/admin_v4/class/get-column-list 专栏详情
     * @apiVersion 4.0.0
     * @apiName  get-column-list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-column-list
     * @apiDescription 专栏详情
     *
     * @apiParam {string} title 标题
     * @apiParam {string} subtitle 副标题
     * @apiParam {string} user  作者相关
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
     * @apiParam {string} status 上架状态
     * @apiParam {string} message 推荐语
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

    public function getColumnList(Request $request)
    {
        $id = $request->get('column_id');
        $list = Column::with('user:id,nickname,headimg')
            ->select('id', 'user_id', 'name', 'title', 'subtitle', 'message', 'status', 'original_price', 'price', 'cover_pic',
                'details_pic', 'created_at', 'timing_online','timing_time')
            ->where('id', $id)->first();
        return success($list);
    }


    /**
     * @api {get} api/admin_v4/class/get-column-work-list 专栏作品列表
     * @apiVersion 4.0.0
     * @apiName  get-column-work-list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-column-work-list
     * @apiDescription  专栏作品列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} id   专栏id
     *
     * @apiSuccess {string}  type   1 视频 2音频 3 文章
     * @apiSuccess {string}  title  标题
     * @apiSuccess {string}  view_num  浏览数
     * @apiSuccess {string}  obj_id    跳转id
     * @apiSuccess {number}  status  状态
     * @apiSuccess {string}  created_at  上架时间
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

    public function getColumnWorkList(Request $request)
    {
        $id = $request->get('id');
        $lists = Works::select('id', 'title', 'type', 'view_num', 'status', 'is_end', 'online_time', 'chapter_num',
            'subscribe_num', 'created_at')
            ->where('column_id', $id)
            ->where('status', '>', 0)
            ->paginate(10)
            ->toArray();
        return success($lists);

    }

    /**
     * @api {get} api/admin_v4/class/get-lecture-work-list 专栏作品信息
     * @apiVersion 4.0.0
     * @apiName  get-lecture-work-list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-lecture-work-list
     * @apiDescription  专栏作品信息
     *
     * @apiParam {string} id   专栏id
     *
     * @apiSuccess {string}  type   1 视频 2音频 3 文章
     * @apiSuccess {string}  title  标题
     * @apiSuccess {string}  view_num  浏览数
     * @apiSuccess {string}  obj_id    跳转id
     * @apiSuccess {number}  status  状态
     * @apiSuccess {string}  created_at  上架时间
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

    public function getLectureWorkList(Request $request)
    {
        $id = $request->get('id');
        $lists = Works::select('id', 'title', 'type', 'view_num', 'status', 'is_end', 'online_time', 'chapter_num',
            'subscribe_num', 'created_at')
            ->where('column_id', $id)
            ->where('status', '>', 0)
            ->first();
        return success($lists);

    }

    /**
     * @api {get} api/admin_v4/class/get-work-chapter-list 作品章节列表
     * @apiVersion 4.0.0
     * @apiName  get-work-chapter-list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-work-chapter-list
     * @apiDescription 作品章节列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} work_id  作品id
     *
     * @apiSuccess {string}  title  标题
     * @apiSuccess {string}  rank   排序
     * @apiSuccess {string}  view_num  观看量
     * @apiSuccess {string}  size      文件大小
     * @apiSuccess {number}  status    状态
     * @apiSuccess {number}  free_trial  是否免费
     * @apiSuccess {string}  timing_time 自动上架时间
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
    public function getWorkChapterList(Request $request)
    {
        $id = $request->get('work_id');
        $lists = WorksInfo::select('id', 'title', 'view_num', 'size', 'status', 'rank', 'free_trial', 'timing_time', 'timing_online', 'created_at')
            ->where('pid', $id)
            ->where('status', '>', 0)
            ->orderBy('rank', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);

    }


    /**
     * @api {post} api/admin_v4/column/delete 删除专栏/讲座
     * @apiVersion 4.0.0
     * @apiName  column/delete
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/column/delete
     * @apiDescription  删除专栏/讲座
     *
     * @apiParam {string} id   专栏id
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
    public function delColumn(Request $request)
    {
        $id = $request->get('id');
        $res = Column::where('id', $id)->update(['status' => 3]);
        if ($res) {
            Works::where('column_id', $id)->update(['status' => 0]);
            return success();
        }
    }

    /**
     * @api {post} api/admin_v4/works/delete 删除听书/讲座
     * @apiVersion 4.0.0
     * @apiName  works/delete
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/works/delete
     * @apiDescription  删除听书/讲座
     *
     * @apiParam {string} id   作品id
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
    public function delWorks(Request $request)
    {
        $id = $request->get('id');
        $res = Works::where('id', $id)->update(['status' => 0]);
        if ($res) {
            WorksInfo::where('pid', $id)->update(['status' => 0]);
            return success();
        }
    }

    /**
     * @api {post} api/admin_v4/chapter/delete 删除章节
     * @apiVersion 4.0.0
     * @apiName  chapter/delete
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/chapter/delete
     * @apiDescription  删除章节
     *
     * @apiParam {string} id   章节id
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
    public function delChapter(Request $request)
    {
        $id = $request->get('id');
        $res = WorksInfo::where('id', $id)->update(['status' => 0]);
        if ($res) {
            $info = WorksInfo::where('id', $id)->first();
            //减少章节总量
            Works::where('id', $info->pid)->decrement('chapter_num');
            return success();
        }
    }

    /**
     * @api {get} /api/admin_v4/works/works_category_data  作品分类
     * @apiName works_category_data
     * @apiVersion 1.0.0
     * @apiGroup  后台-虚拟课程
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * code: 200,
     * msg: "成功",
     * data: [
     * {
     * id: 1,
     * name: "父母关系",
     * pid: 0,
     * level: 1,
     * son: [
     * {
     * id: 3,
     * name: "母子亲密关系",
     * pid: 1,
     * level: 2,
     * son: [ ]
     * }
     * ]
     * },
     * {
     * id: 2,
     * name: "亲子关系",
     * pid: 0,
     * level: 1,
     * son: [ ]
     * }
     * ]
     * }
     */
    public function getWorksCategory(Request $request)
    {
        $category = WorksCategory::select('id', 'name', 'pid', 'level')->where([
            'type' => 1, 'status' => 1,
        ])->orderBy('sort', 'desc')->get()->toArray();
        $data = WorksCategory::getCategory($category, 0, 1);
        return success($data);
    }

    /**
     * @api {get} api/admin_v4/class/get-chapter-info 章节详情
     * @apiVersion 4.0.0
     * @apiName  get-chapter-info
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-chapter-info
     * @apiDescription  章节详情
     *
     * @apiParam   {number} id 章节id
     *
     * @apiSuccess {string}  title  标题
     * @apiSuccess {string}  type   类型  1 视频 2音频 3 文章
     * @apiSuccess {string}  rank   排序
     * @apiSuccess {string}  section   小节
     * @apiSuccess {number}  introduce   简介
     * @apiSuccess {number}  url      视频  音频 地址url
     * @apiSuccess {string}  timing_time 自动上架时间
     * @apiSuccess {string}  video_id    视频id
     * @apiSuccess {string}  created_at  创建时间
     * @apiSuccess {string}  free_trial  是否免费
     * @apiSuccess {string}  timing_online  是否自动上架  1自动 0手动
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
    public function getChapterInfo(Request $request)
    {
        $id = $request->get('id');
        $list = WorksInfo::select('id', 'type', 'title', 'section', 'url', 'online_time', 'timing_online', 'timing_time',
            'status', 'introduce', 'video_id', 'free_trial','rank')
            ->where('id', $id)
            ->first();
        if ($list) {
            $res = WorksInfoContent::where('works_info_id', $id)->first();
            $list['content'] = $res ? $res->content : '';
        }
        return success($list);
    }

    /**
     * @api {post} api/admin_v4/operate/chapter 操作章节
     * @apiVersion 4.0.0
     * @apiName  operate/chapter
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/operate/chapter
     * @apiDescription  操作章节
     *
     * @apiParam {string} id   章节id
     * @apiParam {string} type 类型  1 上线 2 下线 3 免费 4 不免费
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
    public function operateChapter(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type');
        if (!$type) {
            return error(1000, '类型不能为空');
        }
        if ($type) {
            switch ($type) {
                case  1:
                    $data = [
                        'status' => 4,
                        'online_time' => date('Y-m-d H:i:s')
                    ];
                    break;

                case  2:
                    $data = [
                        'status' => 5
                    ];
                    break;
                case 3:
                    $data = [
                        'free_trial' => 1
                    ];
                    break;
                case 4:
                    $data = [
                        'free_trial' => 0
                    ];
                    break;
            }
        }
        $res = WorksInfo::where('id', $id)->update($data);
        if ($res) {
            return success();
        }
    }

    /**
     * @api {post} api/admin_v4/search/category 作品分类
     * @apiVersion 4.0.0
     * @apiName  search/category
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/search/category
     * @apiDescription  作品分类
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
    public function getSearchWorkCategory()
    {
        $lists = WorksCategory::select('id', 'name')->where('level', 2)
            ->orderBy('id', 'desc')
            ->get();
        return success($lists);
    }

}
