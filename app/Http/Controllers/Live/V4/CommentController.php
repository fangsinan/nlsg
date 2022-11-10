<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\BackendLiveDataRole;
use App\Models\BackendLiveRole;
use App\Models\BackendUser;
use App\Models\Live;
use App\Models\User;
use App\Models\LiveComment;
use App\Servers\LiveInfoServers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends ControllerBackend
{
    /**
     * @api {get} api/live_v4/comment/index 评论列表
     * @apiVersion 4.0.0
     * @apiName  comment/index
     * @apiGroup 直播后台-评论列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/index
     * @apiDescription  评论列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {string} nicknake 用户账号
     * @apiParam {string} content  评论内容
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
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
    public function index(Request $request)
    {
        $title = $request->get('title');
        $nickname = $request->get('nickname');
        $content = $request->get('content');
        $start = $request->get('start');
        $end = $request->get('end');
        $live_id = $request->get('live_id');
        $live_flag = $request->get('live_flag');
        $phone = $request->get('phone','');


        $page = $request->get('page') ?? 1;
        $size = $request->get('size') ?? 10;
        //筛查user
        if (!empty($nickname)) {
            $userData = User::select('id')->where('nickname', 'like', '%' . $nickname . '%')->get()->toArray();
            $user_ids = array_column($userData, 'id');
        }
        if (!empty($phone)){
            $check_phone = User::where('phone','like',"$phone%")->pluck('id')->toArray();
            if (!empty($check_phone)){
                $user_ids = $check_phone;
            }
        }

        $son_id = 0;   //渠道标记
        if(!empty($live_flag)){
            $live_role = BackendLiveRole::select('parent_id', 'son_id')->where('son_flag', $live_flag)->first();
            $son_id = $live_role['son_id'];
        }


        //筛查live
        if (!empty($live_id)) {
            $live_ids = [$live_id];
        }else{
            $query = Live::select('id');
//            if ($this->user['live_role'] == 21) {
//                $query->where('user_id', '=', $this->user['user_id'])->where('id','>',52);
//            } elseif ($this->user['live_role'] == 23) {
//                $blrModel = new BackendLiveRole();
//                $son_user_id = $blrModel->getDataUserId($this->user['username']);
//                $query->whereIn('user_id', $son_user_id)->where('id','>',52);
//            }

            //非超管角色可看live
            $live_id_role = IndexController::getLiveRoleIdList($this->user);
            if ($live_id_role !== null){
                if ($live_id_role === []){
                    return success([]);
                }
                $query->whereIn('id',$live_id_role);
            }


            if (!empty($title)) {
                $query->where('title', 'like', '%' . $title . '%');
            }

            $liveData = $query->get()->toArray();
            $live_ids = array_column($liveData, 'id');
        }


        $lists_query = LiveComment::select('id', 'live_id', 'user_id', 'content', 'created_at','live_son_flag')
            ->when($content, function ($query) use ($content) {
                $query->where('content', 'like', '%' . $content . '%');
            })
            ->when($son_id, function ($query) use ($son_id) {
                $query->where('live_son_flag', $son_id);
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            })
            ->where('status', 1)
            ->where('type', 0);

        if ($this->user['role_id'] === 1) {
            $twitter_id_list = null;
        } else {
            $liServers = new LiveInfoServers();
            $twitter_id_list = $liServers->twitterIdList($this->user['username']);
        }

        if ($twitter_id_list !== null) {
            $lists_query->whereIn('live_son_flag', $twitter_id_list);
        }

        if (!empty($live_ids)) {
                $lists_query->whereIn('live_id', $live_ids);
        }

        if (!empty($user_ids)) {
            $lists_query->whereIn('user_id', $user_ids);
        }

//        $lists = $lists_query->orderBy('id', 'desc')
//            ->paginate(10)
//            ->toArray();
        $total = $lists_query->count();
        $list = $lists_query
//            ->orderBy('id', 'desc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();
        $lists = [
            'total' => $total,
            'data'  => $list ?? []
        ];

        $son_id_list = [];
        foreach ($lists['data'] as $key => $val) {
            $val['user'] = [];  //初值
            $val['live'] = [];

            $lists_user_ids[] = $val['user_id'];
            $lists_live_ids[] = $val['live_id'];
            $son_id_list[] = $val['live_son_flag'];
        }

        $new_userData = [];
        if (!empty($lists_user_ids)) {
            $list_userData = User::select('id', 'nickname','phone')->whereIn('id', $lists_user_ids)->get()->toArray();
            foreach ($list_userData as $key => $val) {
                $new_userData[$val['id']] = $val;
            }
        }
        $son_id_list_data = [];
        if (!empty($son_id_list) && empty($content)){
            $son_id_list_data = BackendLiveRole::select('son_id', 'son_flag','son')
                ->whereIn('son_id',$son_id_list)
                ->get();
        }

        $new_list_liveData = [];
        if (!empty($lists_live_ids)) {
            $list_liveData = Live::select('id', 'title')->whereIn('id', $lists_live_ids)->get()->toArray();
            foreach ($list_liveData as $key => $val) {
                $new_list_liveData[$val['id']] = $val;
            }
        }

        foreach ($lists['data'] as $key => &$val) {

            if (!empty($new_userData[$val['user_id']])) {
                $val['user'] = $new_userData[$val['user_id']];
            }

            if (!empty($new_list_liveData)) {
                $val['live'] = $new_list_liveData[$val['live_id']];
            }
            $val['son_phone'] = '';
            $val['son_id'] = 0;
            $val['son_flag'] = '';
            if (!empty($son_id_list_data)){
                foreach ($son_id_list_data as $sldv){
                    if ($val['live_son_flag'] === $sldv['son_id']){
                        $val['son_phone'] = $sldv['son'];
                        $val['son_id'] = $sldv['son_id'];
                        $val['son_flag'] = $sldv['son_flag'];
                    }
                }
            }
        }


        return success($lists);
    }





    /**
     * @api {get} api/live_v4/live_comment/listExcel 评论下载
     * @apiVersion 4.0.0
     * @apiName  comment/index
     * @apiGroup 直播后台-评论列表下载
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/index
     * @apiDescription  评论列表
     *
     * @apiParam {number} live_id 直播id
     * @apiParam {number} live_flag 直播渠道
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
    public function listExcel(Request $request)
    {

        $live_id = $request->get('live_id');
        $live_flag = $request->get('live_flag');

        if(empty($live_id)) {
            return $this->error(0,'live_id 为空');
        }

        $columns = ['用户id', '用户昵称', '用户手机', '评论', '时间','渠道名称'];
        $fileName = 'lc' . date('Y-m-d H:i') .rand(10,99). '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'a');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中

        $size = 100;
        $page = 1;
        $request->offsetSet('size', $size);
        $request->offsetSet('excel_flag', '1');
        $while_flag = true;
        while ($while_flag) {
            $list_query = LiveComment::with([ 'user:id,phone,nickname',])
                ->select('id', 'live_id', 'user_id', 'content', 'created_at','live_son_flag')
                ->where('live_id', $live_id)
                ->where('type', 0)
                ->where('status', 1);
            if(!empty($live_flag)){
                $list_query->where('live_son_flag', $live_flag);
            }
            $list = $list_query->orderBy('id', 'desc')
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->get();

            if ($list->isEmpty()) {
                $while_flag = false;
            } else {
                foreach ($list as $v) {
                    $temp_v = [];
                    $temp_v['user_id']  = '`' . ($v->user_id ?? '');
                    $temp_v['nickname'] = $v->user->nickname ?? '';
                    $temp_v['phone']    = '`' . ($v->user->phone ?? '');
                    $temp_v['content']  = $v->content ?? '';
                    $temp_v['created_at']  = $v->created_at ?? '';
                    $temp_v['live_son_flag'] = '';
                    $temp_v['live_son_phone'] = '';
                    if (!empty($v->live_son_flag ?? '')){
                        $temp_check_son_flag = BackendLiveRole::select('son_id', 'son_flag','son')
                            ->where('son_id','=',$v->live_son_flag)
                            ->first();
                        if (!empty($temp_check_son_flag)){
                            $temp_v['live_son_flag'] = $temp_check_son_flag->son_flag;
//                            $temp_v['live_son_phone'] = $temp_check_son_flag->son;
                        }
                    }
                    mb_convert_variables('GBK', 'UTF-8', $temp_v);
                    fputcsv($fp, $temp_v);
                    ob_flush();     //刷新输出缓冲到浏览器
                    flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
                }
                $page++;
            }
        }

        fclose($fp);
        exit();
    }



    /**
     * @api {get} api/live_v4/comment/show 评论查看
     * @apiVersion 4.0.0
     * @apiName  comment/show
     * @apiGroup 直播后台-评论查看
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/show
     * @apiDescription  评论查看
     *
     * @apiParam {number} id  评论id
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
    public function show(Request $request)
    {
        $id = $request->get('id');
        $list = LiveComment::with('user:id,nickname')
            ->select('id', 'user_id', 'content', 'created_at')
            ->where('id', $id)
            ->first();
        if ( ! $list) {
            return error(1000, '评论不存在');
        }
        return success($list);
    }

    /**
     * @api {post} api/live_v4/comment/delete 直播评论删除
     * @apiVersion 4.0.0
     * @apiName  comment/delete
     * @apiGroup 直播后台
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/delete
     * @apiDescription  直播评论删除
     *
     * @apiParam {number} id 直播评论id
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
    public function delete(Request $request)
    {
        $id   = $request->input('id');
        $list = LiveComment::where('id', $id)->first();
        if ( !$list) {
            return error(1000, '直播评论不存在');
        }
        $res = LiveComment::where('id', $id)->update(['status' => 0]);
        if ($res) {
            return success();
        }
    }
}
