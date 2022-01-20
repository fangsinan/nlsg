<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\ShortVideoModel;
use Illuminate\Http\Request;


class VideoController extends ControllerBackend
{


    /**
     * @api {get} api/admin_v4/video/video-list 短视频列表
     * @apiVersion 4.0.0
     * @apiName  video-list
     * @apiGroup 后台-短视频
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/video/video-list
     * @apiDescription 短视频列表
     *
     * @apiParam {number} page 分页
     * @apiParam {number} id 视频id
     * @apiParam {string} title 标题
     * @apiParam {number} status 上下架
     * @apiParam {string} sort  排序 浏览view_num 评论comment_num  点赞like_num   分享share_num
     * @apiParam {string} sort_type  排序 asc  desc
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

    public function video_list(Request $request)
    {


        $video_id = $request->get('id');
        $title = $request->get('title');
        $status = $request->get('status');
        $sort = $request->get('sort') ?? 'created_at';
        $video_type = $request->get('video_type') ?? 0;
        $sort_type = $request->get('sort_type') ?? 'desc';

        $query = ShortVideoModel::select([
            'id', 'title', 'introduce', 'status', 'share_img', 'cover_img', 'user_id', 'online_time', 'attribute_url','video_type','view_num',
            'comment_num','like_num','share_num','callback_url','url','video_id'
        ])
            ->when($video_type, function ($query) use ($video_type) {
                $query->where('video_type', $video_type);
            })
            ->when($video_id, function ($query) use ($video_id) {
                $query->where('id', $video_id);
            })
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%'.$title.'%');
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            });

        $lists = $query->where('status', '>', 0)
            ->orderBy($sort, $sort_type)
            ->paginate(10)
            ->toArray();

//        if ($lists['data']) {
//            foreach ($lists['data'] as &$v) {
//
//            }
//        }

        return success($lists);

    }






    /**
     * @api {post} api/admin_v4/video/add-video-info 增加/编辑短视频
     * @apiVersion 5.0.0
     * @apiName  add-video-info
     * @apiGroup 后台-短视频
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/video/add-video-info
     * @apiDescription 增加/编辑短视频
     *
     * @apiParam {number} id   章节id  存在为编辑
     * @apiParam {string} title 标题
     * @apiParam {string} introduce 简介
     * @apiParam {string} url    音视频url
     * @apiParam {string} status 状态 0 删除 1 未审核  2通过 3下架'
     * @apiParam {number} video_id 视频id
     * @apiParam {string} cover_img 封面
     * @apiParam {string} detail_img 封面
     * @apiParam {string} share_img  分享图
     * @apiParam {string} user_id  user_id
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
    public function addVideoInfo(Request $request)
    {
        $data= $request->getContent();
        $input = json_decode($data,true);
//        $input = $request->all();
        $id = $request->get('id');
        $video = ShortVideoModel::where('id', $id)->first();
        if (!empty($id) && empty($video)) {
            return error(1000, '视频不存在');
        }

        $online_time = null;
        $status = $input['status']??1;
        if($status == 2){
            $online_time = date("Y-m-d H:i:s");
        }
        if(empty($input['title'])) {
            return error(1000, '标题不存在');
        }
        if(empty($input['introduce'])) {
            return error(1000, '简介不存在');
        }
        if(empty($input['url']) && empty($input['video_id'])) {
            return error(1000, '视频链接错误');
        }
        $data = [
            'title'         => $input['title'] ?? "",
            'introduce'     => $input['introduce'] ?? "",
            'status'        => $status,
            'share_img'     => $input['share_img'] ?? '',
            'cover_img'     => $input['cover_img'] ?? '',
            'detail_img'    => $input['detail_img'] ?? '',
            'user_id'       => $input['user_id'] ?? 0,
            'rank'          => $input['rank'] ?? 0,
            'online_time'   => $online_time,
//            'view_num'      => $input['view_num'] ?? '',
            'video_id'      => $input['video_id'] ?? 0,
            'url'           => $input['url'] ?? 0,
//            'duration'      => $input['duration'] ?? 0,
//            'size'          => $input['size'] ?? 0,
//            'attribute_url' => $input['attribute_url'] ?? 0,
//            'video_type'    => $input['video_type'] ?? 1,
        ];


        if (!empty($input['id'])) {
            ShortVideoModel::where('id', $input['id'])->update($data);
        } else {
            ShortVideoModel::create($data);

        }
        return success();

    }



    /**
     * @api {post} api/admin_v4/video/del-video 删除短视频
     * @apiVersion 5.0.0
     * @apiName  del-video
     * @apiGroup 后台-短视频
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/video/del-video
     * @apiDescription 删除短视频
     *
     * @apiParam {number} id   id
     * @apiParam {string} status 状态  0 删除 1 未审核  2上架 3下架
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
    public function delVideoInfo(Request $request)
    {
        $id = $request->get('id')??0;
        $status = $request->get('status');

        if(!empty($status)){
            $data['status'] = $status;
            if($status == 2){
                $data['online_time'] = date('Y-m-d H:i:s');
            }

            $res = ShortVideoModel::where('id', $id)->update($data);
            if($res){
                return success();
            }else{
                return error(1000, '删除错误');
            }
        }else{
            return error(1000, '状态有误');
        }
    }
}