<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClassController extends Controller
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
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/class/lecture 讲座列表
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
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/class/works 精品课
     * @apiVersion 4.0.0
     * @apiName  works
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/works
     * @apiDescription 精品课
     *
     * @apiParam {number} page 分页
     * @apiParam {number} work_id 编号
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

        $query = Works::with('user:id,nickname')
            ->when($work_id, function ($query) use ($work_id) {
                $query->where('id', $work_id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
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

        $lists = $query->select('id', 'title', 'type', 'is_end', 'created_at', 'user_id', 'view_num', 'status', 'price', 'is_end', 'chapter_num')
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
        $online_type = $input['online_type'] ?? 1;

        $data = [
            'cover_pic' => $cover_pic,
            'details_pic' => $details_pic,
            'name' => $name ?? '',
            'subtitle' => $subtitle,
            'message' => $message,
            'user_id' => $user_id,
            'price' => $price,
            'original_price' => $original_price
        ];

        if (!empty($input['id'])) {
            Column::where('id', $input['id'])->update($data);
        } else {
            Column::create($data);
        }

        return success('操作成功');

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
        $online_type = $input['online_type'] ?? 1;

        $data = [
            'cover_pic' => $cover_pic,
            'details_pic' => $details_pic,
            'name' => $name,
            'subtitle' => $subtitle,
            'message' => $message,
            'user_id' => $user_id,
            'price' => $price,
            'original_price' => $original_price
        ];
        if (!empty($input['id'])) {
            Column::where('id', $input['id'])->update($data);
        } else {
            Column::create($data);
        }

        return success('操作成功');
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
     * @apiParam {string} user_id 作者
     * @apiParam {string} original_price 定价
     * @apiParam {string} price 售价
     * @apiParam {string} is_end 是否完结
     * @apiParam {number} timing_online 是否自动上架
     * @apiParam {string} status 上架状态
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
        $cover_img = covert_img($input['cover_img']) ?? '';
        $detail_img = covert_img($input['detail_img']) ?? '';
        $user_id = $input['user_id'] ?? 0;
        $original_price = $input['original_price'] ?? 0;
        $price = $input['price'] ?? 0;
        $is_end = $input['is_end'] ? 1 : 0;
        $status = $input['status'] ?? 5;  //0 删除 1 待审 2 拒绝  3通过 4上架 5下架
        $timing_online = $input['online_type'] ?? 0; //是否自动上架  1自动 0手动
        $content = $input['content'] ?? '';

        $data = [
            'title' => $title,
            'cover_img' => $cover_img,
            'detail_img' => $detail_img,
            'user_id' => $user_id,
            'original_price' => $original_price,
            'price' => $price,
            'is_end' => $is_end,
            'status' => $status,
            'content' => $content
        ];
        if (!empty($input['id'])) {
            Works::where('id', $input['id'])->update($data);
        } else {
            Works::create($data);
        }

        return success('操作成功');
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
     *   "data": {
     *
     *    }
     * }
     */
    public function getWorkList(Request $request)
    {
        $id = $request->get('id');
        $work = Works::with('userName:id,nickname')
            ->select('id', 'title', 'cover_img', 'detail_img', 'content', 'status', 'user_id', 'is_end', 'view_num',
                'price', 'original_price')
            ->first();
        return $work;
    }

    /**
     * @api {post} api/admin_v4/class/add-works-chapter 增加章节
     * @apiVersion 4.0.0
     * @apiName  add-chapter
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-works-chapter
     * @apiDescription 增加章节
     *
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


        $timing_online = $input['timing_online'] ?? 0;
        if ($timing_online == 1) {
            $data['timing_time'] = date('Y-m-d H:i:s', time());
        }

        $data = [
            'pid' => $work_id,
            'type' => $input['type'] ?? '',
            'title' => $input['title'] ?? '',
            'section' => $input['section'] ?? '',
            'introduce' => $input['introduce'] ?? '',
            'url' => $input['url'] ?? '',
            'status' => $input['status'] ?? 5,
            'video_id' => $input['video_id'] ?? '',
            'free_trial' => $input['free_trial'] ?? 0,
            'timing_online' => $timing_online ?? 0
        ];

        $res = WorksInfo::create($data);
        if ($res) {
            return success('创建成功');
        }
    }

    /**
     * @api {post} api/admin_v4/class/add-listen 增加听课
     * @apiVersion 4.0.0
     * @apiName  add-listen
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-listen
     * @apiDescription 创建精品课
     *
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
        $data['title'] = $input['title'] ?? '';
        if (!$data['title']) {
            return error('标题不能为空');
        }
        $data['cover_img'] = covert_img($input['cover_img']) ?? '';
        $data['detail_img'] = covert_img($input['detail_img']) ?? '';
        $data['user_id'] = $input['user_id'] ?? 0;
        $data['original_price'] = $input['original_price'] ?? 0;
        $data['is_end'] = $input['is_end'] ? 1 : 0;
        $data['status'] = $input['status'] ?? 5;  //0 删除 1 待审 2 拒绝  3通过 4上架 5下架
        $data['timing_online'] = $input['online_type'] ?? 0; //是否自动上架  1自动 0手动
        $data['content'] = $input['content'] ?? '';

        $res = Works::create($data);
        if ($res) {
            return success('创建成功');
        }

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
            ->select('id', 'user_id', 'title', 'subtitle', 'message', 'status', 'original_price', 'price', 'cover_pic',
                'details_pic', 'created_at',)
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
            ->paginate(10)
            ->toArray();

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
            ->orderBy('rank', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);

    }


}