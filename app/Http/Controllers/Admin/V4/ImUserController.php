<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\History;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Servers\ImUserServers;
use Illuminate\Http\Request;

class ImUserController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/im_user/list 用户列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_user/list
     * @apiGroup 后台-用户列表与信息
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_user/list
     * @apiDescription 用户列表
     * @apiParam {number} [id] 用户id,详情使用
     * @apiParam {number=-1,0,1,2} [sex] 性别(0位置,1男,2女,-1全部)
     * @apiParam {number=0,1,2} [order_type] 订单状态(0全部  1已完成  2未完成)
     * @apiParam {string} [created_at] 时间(1一个月内,2三个月内,其他格式(20201-01-01,2020-04-04))
     *
     * @apiSuccess {string} id 用户id
     * @apiSuccess {string} phone 账号
     * @apiSuccess {string} nickname 昵称
     * @apiSuccess {string} headimg 头像
     * @apiSuccess {string} sex 性别
     * @apiSuccess {string} created_at 注册时间
     * @apiSuccess {string} birthday 生日
     * @apiSuccess {string} intro 简介
     * @apiSuccess {string} reply_num 评论和@数量
     * @apiSuccess {string} fan_num 粉丝人数
     * @apiSuccess {string} follow_num 关注人数
     * @apiSuccess {string} fans_num 新增粉丝人数
     * @apiSuccess {string} open_count vip开通次数
     *
     * @apiSuccess {string[]} im_user im注册信息(如果空,表示没注册过im)
     *
     * @apiSuccess {string[]} vip_user 会员信息
     * @apiSuccess {string} vip_user.level 级别
     * @apiSuccess {string} vip_user.created_at 开始时间
     * @apiSuccess {string} vip_user.expire_time 到期时间
     *
     * @apiSuccess {string[]} statistics 统计信息
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1627376395,
     * "data": {
     * "list": {
     * "current_page": 1,
     * "data": [
     * {
     * "id": 316307,
     * "phone": "13847752606",
     * "nickname": "138****2606",
     * "headimg": "image/202009/13f952e04c720a550193e5655534be86.jpg",
     * "sex": 0,
     * "created_at": null,
     * "birthday": null,
     * "intro": "",
     * "is_staff": 0,
     * "status": 1,
     * "ios_balance": "0.00",
     * "is_author": 0,
     * "income_num": 0,
     * "reply_num": 0,
     * "fan_num": 0,
     * "follow_num": 0,
     * "fans_num": 0,
     * "ref": 0,
     * "is_test_pay": 0,
     * "open_count": 1,
     * "im_user": null,
     * "vip_user": {
     * "id": 3779,
     * "user_id": 316307,
     * "level": 1,
     * "is_open_360": 0,
     * "created_at": "2020-10-27 21:11:46",
     * "expire_time": "2021-10-27 00:00:00",
     * "time_begin_360": null,
     * "time_end_360": null
     * }
     * }
     * ],
     * "first_page_url": "http://127.0.0.1:8000/api/admin_v4/im_user/list?page=1",
     * "from": 1,
     * "last_page": 1,
     * "last_page_url": "http://127.0.0.1:8000/api/admin_v4/im_user/list?page=1",
     * "next_page_url": null,
     * "path": "http://127.0.0.1:8000/api/admin_v4/im_user/list",
     * "per_page": 10,
     * "prev_page_url": null,
     * "to": 1,
     * "total": 1
     * },
     * "statistics": {
     * "all": 258354,
     * "man": 16019,
     * "woman": 32497,
     * "unknown": 209815
     * }
     * }
     * }
     */
    public function list(Request $request)
    {
        $servers = new ImUserServers();
        $data = $servers->list($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }


    /**
     * @api {get} api/admin_v4/im_user/friends_list 好友列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_user/friends_list
     * @apiGroup 后台-用户列表与信息
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_user/friends_list
     * @apiDescription 好友列表
     */
    public function friendsList(Request $request)
    {
        $servers = new ImUserServers();
        $data = $servers->friendsList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    public function orderList(Request $request)
    {
        $servers = new ImUserServers();
        $data = $servers->orderList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    public function mallOrderList(Request $request)
    {
        $servers = new ImUserServers();
        $data = $servers->mallOrderList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }


    public function history(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $order = $request->input('order', 'desc');

        if (empty($user_id)){
            return $this->getRes([
                'code'=>false,
                'msg'=>'用户错误',
            ]);
        }

        $lists = History::where(['user_id' => $user_id, 'is_del' => 0,])
            ->groupBy('relation_id', 'relation_type')->orderBy('updated_at', $order)->paginate($this->page_per_page)->toArray();

        if (empty($lists['data'])) {
            return $this->success();
        }
        $new_list = [];
        foreach ($lists['data'] as $key => $val) {
            //查询所属专栏 课程 以及章节
            $val['column_name'] = '';
            $val['column_cover_img'] = '';
            $val['works_name'] = '';
            $val['works_cover_img'] = '';
            $val['worksInfo_name'] = '';


            if ($val['relation_type'] == 1 or $val['relation_type'] == 2 or $val['relation_type'] == 5) {
//                $column = Column::find($val['relation_id']);
                $column = Column::where(['id'=>$val['relation_id'],'status'=>1])->first();
                $val['column_name'] = $column['name'] ?? '';
                $val['column_cover_img'] = $column['cover_pic'] ?? '';
            }
            if ($val['relation_type'] == 3 or $val['relation_type'] == 4) {
//                $works = Works::find($val['relation_id']);
                $works = Works::where(['id'=>$val['relation_id'],'status'=>4])->first();
                $val['works_name'] = $works['title'] ?? '';
                $val['works_cover_img'] = $works['cover_img'] ?? '';
            }
            if ($val['info_id']) {
                $worksInfo = WorksInfo::find($val['info_id']);
                $val['worksInfo_name'] = $worksInfo['title'] ?? '';
                $val['worksInfo_type'] = $worksInfo['type'] ?? "";
            }
//            $new_list[History::DateTime($val['created_at'])][] = $val;

            if($val['column_name'] || $val['works_name'] ){
                $list[History::DateTime($val['created_at'])][] = $val;
                $new_list[History::DateTime($val['created_at'])][] = $val;
            }
        }

        return $this->getRes($new_list);
    }

}
