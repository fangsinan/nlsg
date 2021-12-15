<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;

use App\Models\ShortVideoLikeModel;
use App\Models\ShortVideoModel;
use Illuminate\Http\Request;

class VideoController extends Controller
{


    /**
     * @api {get} /api/v4/video/get_random_video 获取短视频信息
     * @apiName get_random_video
     * @apiVersion 5.0.0
     * @apiGroup five_video
     *
     * @apiParam {int} page
     * @apiParam {int} id      视频id
     * @apiParam {int} top_id  当前播放的id  防止重复获取数据
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
        {
            code: 200,
            msg: "成功",
            now: 1639375255,
            data: {
                id: 1,
                user_id: 211172,
                share_img: "",
                title: "第一个",
                introduce: "",
                view_num: 0,
                like_num: 0,
                comment_num: 0,
                share_num: 0,
                duration: "",
                url: "",
                user_info: {
                    name: "房思楠",
                    title: "",
                    subtitle: "",
                    headimg: "https://image.nlsgapp.com/image/202009/13f952e04c720a550193e5655534be86.jpg",
                    is_follow: 0,
                    is_like: 0
                }
            }
        }
     */
    public function getRandomVideo(Request $request)
    {
        $uid = $this->user['id'] ?? 0;

        $page = $request->input('page') ??1;
        $top_id = $request->input('top_id') ??0;
        $id = $request->input('id') ??0;

        $videoObj = new ShortVideoModel();
        $relation_id = $videoObj->getVideo($uid,$id,$top_id,$page,3);

        return $this->success($relation_id);
    }



    /**
     * @api {get} /api/v4/video/like 短视频点赞
     * @apiName like
     * @apiVersion 5.0.0
     * @apiGroup five_video
     *
     * @apiParam  id  短视频id、
     * @apiParam  type  类型 1短视频
     * @apiParam  is_like 1点赞  0取消
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
        $id   = $request->input('id')??0;
        $type = $request->input('type')??1;
        $is_like = $request->input('is_like') ??0 ;

        $uid = $this->user['id'] ?? 0;

        $videoLikeObj = new ShortVideoLikeModel();
        $videoLikeObj->Like($id,$type,$is_like,$uid);
        return success();
    }




}
