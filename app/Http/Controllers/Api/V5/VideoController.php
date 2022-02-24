<?php


namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;

use App\Models\ShortVideoLikeModel;
use App\Models\ShortVideoModel;
use App\Models\ShortVideoShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{


    /**
     * @api {get} /api/v5/video/get_random_video 获取短视频信息
     * @apiName get_random_video
     * @apiVersion 5.0.0
     * @apiGroup five_video
     *
     * @apiParam {int} id      需要请求的视频id
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
        $is_home = $request->input('is_home');
        $id = $request->input('id') ??0;

        $videoObj = new ShortVideoModel();
        $relation_id = $videoObj->getVideo($uid,$id,$top_id,$page,3,$is_home);

        return $this->success($relation_id);
    }



    /**
     * @api {get} /api/v5/video/like 短视频点赞
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
        $res = $videoLikeObj->Like($id,$type,$is_like,$uid);
        return $this->getRes($res);

    }

    /**
     * @api {get} /api/v5/video/show 短视频阅读增加
     * @apiName show
     * @apiVersion 5.0.0
     * @apiGroup five_video
     *
     * @apiParam  id  短视频id、
     * @apiParam  is_finish 是否完播
     * @apiParam  show_id  当前阅读记录id
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
    public function show(Request $request)
    {
        $id   = $request->input('id')??0;//多个用逗号拼接
        $is_finish   = $request->input('is_finish')??0;

        if(!empty($id)){
            ShortVideoModel::readVideo(explode(',', $id), $is_finish);
        }
        // 返回 3秒阅读
        $uid = $this->user['id'] ?? 0;
        $res = ['show_id'=>0];
        if(!empty($uid)){
            //   完播再请求一次
            $show_id   = $request->input('show_id')??0;// 当前记录id
            if(empty($show_id)){
                $show = ShortVideoShow::create([
                    'relation_id' => $id,
                    'user_id'     => $uid,
                    'is_finish'   => $is_finish,
                    'is_finish'   => $is_finish,
                ]);
                $res['show_id'] = $show->id;

            }else{
                ShortVideoShow::where(['id'=>$show_id])->update(['is_finish'   => $is_finish]);
            }

        }




        return $this->getRes($res);


    }



}
