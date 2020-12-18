<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Servers\MallCommentServers;
use Illuminate\Http\Request;

class MallCommentController extends ControllerBackend
{
    /**
     * 回复评论
     * @api {post} /api/admin_v4/goods/comment_reply 回复评论
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/comment_reply
     * @apiGroup  后台-商品评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_reply
     * @apiParam {number} id
     * @apiParam {string} [content] 回复评论
     * @apiDescription 评论列表
     */
    public function replyComment(Request $request){
        $servers = new MallCommentServers();
        $data = $servers->replyComment($request->input(),$this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 评论状态变更
     * @api {put} /api/admin_v4/goods/comment_status 评论状态变更
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/comment_status
     * @apiGroup  后台-商品评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_status
     * @apiParam {number} id
     * @apiParam {string=on,off} flag on显示,off隐藏
     * @apiDescription 评论状态变更
     */
    public function changeStatus(Request $request){
        $servers = new MallCommentServers();
        $data = $servers->changeStatus($request->input());
        return $this->getRes($data);
    }

    /**
     * 评论列表
     * @api {post} /api/admin_v4/goods/comment_list 评论列表
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/comment_list
     * @apiGroup  后台-商品评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_list
     * @apiParam {number} [is_robot] 1是虚拟评论 0不是
     * @apiParam {string} [content] 评论内容
     * @apiParam {string} [goods_name] 商品名称
     * @apiDescription 评论列表
     */
    public function commentList(Request $request)
    {
        $servers = new MallCommentServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * 添加虚拟评论
     * @api {post} /api/admin_v4/goods/add_robot_comment 添加虚拟评论
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/goods/add_robot_comment
     * @apiGroup  后台-商品评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/goods/add_robot_comment
     * @apiDescription 添加虚拟评论
     * @apiParamExample {json} Request-Example:
     * {
     * "goods_id":474,
     * "sku_number":"1611238695",
     * "list":[
     * {
     * "content":"好啊",
     * "picture":""
     * },
     * {
     * "content":"好啊11",
     * "picture":""
     * },
     * {
     * "content":"好啊11",
     * "picture":""
     * }
     * ]
     * }
     */
    public function addRobotComment(Request $request)
    {
        $servers = new MallCommentServers();
        $data = $servers->addRobotComment($request->input());
        return $this->getRes($data);
    }
}
