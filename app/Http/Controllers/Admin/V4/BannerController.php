<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Util\PHP\AbstractPhpProcess;

class BannerController extends ControllerBackend
{

    /**
     * @api {get} api/admin_v4/banner/list 广告列表
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/banner/list
     * @apiDescription 专栏列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     *
     * @apiSuccess {string} type  1. 首页   (50段商城预留)51.商城首页轮播  52.分类下方推荐位  53.爆款推荐
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} rank    排序
     * @apiSuccess {string} jump_type  跳转类型 1:h5(走url,其他都object_id)  2:商品  3:优惠券领取页面4精品课 5.讲座 6.听书 7 360
     * @apiSuccess {string}  url    h5链接
     * @apiSuccess {string}  obj_id  跳转id
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
    public function  list(Request $request)
    {
        $title = $request->get('title');
        $type = $request->get('type');
        $start = $request->get('start');
        $end = $request->get('end');
        $status = $request->get('status',0);
        $query = Banner::when($title, function ($query) use ($title) {
            $query->where('name', 'like', '%' . $title . '%');
        })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            })
            ->when($status,function ($query) use ($status){
                $query->where('status',$status);
            });

        $query->where('app_project_type','=',APP_PROJECT_TYPE);
        $lists = $query->select('id', 'title', 'pic', 'rank', 'url', 'type', 'jump_type', 'obj_id', 'created_at', 'status')
            ->orderBy('rank')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     * @api {post} api/admin_v4/banner/add 创建广告
     * @apiVersion 4.0.0
     * @apiName  banner/add
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/banner/add
     * @apiDescription 创建广告
     *
     * @apiParam {string} id   广告id(编辑操作)
     * @apiParam {string} title 标题
     * @apiParam {string} pic   图片
     * @apiParam {string} url   h5地址
     * @apiParam {string} rank  排序
     * @apiParam {number} type  跳转类型
     * @apiParam {string} obj_id 跳转id
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
    public function add(Request $request)
    {
        $input = $request->all();
        $title = $input['title'] ?? '';
        if (!$title) {
            return error(1000, '标题不能为空');
        }
        $id = $input['id'] ?? 0;
        $pic = !empty($input['pic']) ? covert_img($input['pic']) : '';
        $url = $input['url'] ?? '';
        $rank = $input['rank'] ?? 99;
        $type = $input['type'] ?? 0;
        $status = $input['status'] ?? 2;
        $objid = $input['obj_id'] ?? 0;
        $jump_type = $input['jump_type'] ?? 0;
        $start_time = $input['start_time'] ?? 0;
        $end_time = $input['end_time'] ?? 0;

        if (empty($start_time)) {
            $start_time = null;
        } else {
            $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
        }

        if (empty($end_time)) {
            $end_time = null;
        } else {
            $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
        }

        $data = [
            'title' => $title,
            'pic' => $pic,
            'url' => $url,
            'rank' => $rank,
            'type' => $type,
            'jump_type' => $jump_type,
            'obj_id' => $objid,
            'status' => $status,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];

        if (!empty($id)) {
            Banner::where('id', $id)->update($data);
        } else {
            Banner::create($data);
        }

        return success();
    }

    /**
     * @api {get} api/admin_v4/banner/edit 广告编辑
     * @apiVersion 4.0.0
     * @apiName  banner/edit
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/banner/edit
     * @apiDescription  广告编辑
     *
     * @apiParam {string} title 标题
     * @apiParam {string} pic   图片
     * @apiParam {string} url   h5地址
     * @apiParam {string} rank  排序
     * @apiParam {number} type  位置
     * @apiParam {number} jump_type 跳转类型
     * @apiParam {string} obj_id 跳转id
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
    public function edit(Request $request)
    {
        $id = $request->get('id');
        $list = Banner::select('id', 'title', 'pic', 'url', 'rank', 'type',
            'jump_type', 'obj_id', 'status', 'start_time', 'end_time')
            ->where('id', $id)
            ->first();
        if (!$list) {
            return error(1000, '广告不存在');
        }
        return success($list);
    }

}
