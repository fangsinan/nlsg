<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\User;
use App\Models\UserWechat;
use App\Models\UserWechatName;
use App\Servers\UserWechatServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserWechatController extends ControllerBackend
{

    public function test(){
        $UserWechatServers = new UserWechatServers();
        $res=$UserWechatServers->get_user_info('wok8dJEQAAdOT37rRpX-CUZCuFYPWt5Q');
        var_dump($res);
    }

    /**
     * @api {get} api/admin_v4/user_wechat/search_wechat_user_list 获取微信客户列表
     * @apiVersion 4.0.0
     * @apiName  user_wechat/search_wechat_user_list
     * @apiGroup 后台-微信客户管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/search_wechat_user_list
     * @apiDescription 微信客户列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} name 客户名称
     * @apiParam {string} phone 手机号
     * @apiParam {string} follow_user_userid 跟进客户员工userid
     * @apiParam {string} source_follow_user_userid 来源的客户员工id
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
    public function search_wechat_user_list(Request $request){

        $params= $request->input();

        $query = UserWechat::with(
            [
                'user:id,nickname,phone,unionid',
                'follow_staff:id,qw_name,follow_user_userid',
                'source_staff:id,qw_name,follow_user_userid',
            ])
            ->when(!empty($params['name']), function ($query) use ($params) {
                $query->where('name', $params['name']);
            })
            ->when(!empty($params['phone']), function ($query) use ($params) {
                $query->whereHas('user', function ($query) use ($params) {
                    $query->where('phone',  $params['phone'] );
                });
            })
            ->when(!empty($params['follow_user_userid']), function ($query) use ($params) {
                $query->whereHas('from_staff', function ($query) use ($params) {
                    $query->where('follow_user_userid',  $params['follow_user_userid'] );
                });
            })
            ->when(!empty($params['source_follow_user_userid']), function ($query) use ($params) {
                $query->whereHas('source_staff', function ($query) use ($params) {
                    $query->where('follow_user_userid',  $params['source_follow_user_userid'] );
                });
            });

        $lists = $query->paginate(10)->toArray();

        return success($lists);
    }


    /**
     * @api {get} api/admin_v4/user_wechat/search_wechat_staff_user_list 获取员工列表
     * @apiVersion 4.0.0
     * @apiName  user_wechat/search_wechat_staff_user_list
     * @apiGroup 后台-获取员工列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/search_wechat_staff_user_list
     * @apiDescription 获取员工列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} name 名称
     * @apiParam {string} userid 员工userid
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
    public function search_wechat_staff_user_list(Request $request){

        $params= $request->input();
        $query = UserWechatName::query()
            ->when(!empty($params['name']), function ($query) use ($params) {
                $query->where('qw_name', 'like', '%' . $params['name'] . '%');
            })
            ->when(!empty($params['userid']), function ($query) use ($params) {
                $query->where('follow_user_userid', $params['userid']);
            });

        $lists = $query->paginate(10)->toArray();

        return success($lists);
    }


    /**
     * @api {get} api/admin_v4/user_wechat/get_wechat_staff_user_list 获取员工列表
     * @apiVersion 4.0.0
     * @apiName  user_wechat/get_wechat_staff_user_list
     * @apiGroup 后台-获取员工列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/get_wechat_staff_user_list
     * @apiDescription 获取员工列表
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
    public function get_wechat_staff_user_list(Request $request){

        $params= $request->input();
        $query = UserWechatName::query()
            ->when(!empty($params['name']), function ($query) use ($params) {
                $query->where('qw_name', 'like', '%' . $params['name'] . '%');
            })
            ->when(!empty($params['userid']), function ($query) use ($params) {
                $query->where('follow_user_userid', $params['userid']);
            });

        $lists = $query->get()->toArray();

        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/user_wechat/transfer_customer 分配在职成员的客户
     * @apiVersion 4.0.0
     * @apiName  user_wechat/transfer_customer
     * @apiGroup 后台-分配在职成员的客户
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/transfer_customer
     * @apiDescription 分配在职成员的客户
     *
     * @apiParam {number} handover_userid 原跟进成员的userid
     * @apiParam {string} takeover_userid 接替成员的userid
     * @apiParam {string} userids 客户id 多个逗号拼接
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
    public function transfer_customer(Request $request){

        $UserWechatServers = new UserWechatServers();
        $data=$request->input();
        $data=[
            'handover_userid'=>'FangSiNan',
            'takeover_userid'=>'HanJian',
            'userids'=>'159761',
        ];
        $res=$UserWechatServers->transfer_customer($data);

        if(!checkRes($res)){
            return error(0,$res);
        }

        return success();
    }

    //定时任务执行 一小时执行一次
    /**
     * @api {get} api/admin_v4/user_wechat/transfer_result 查询转移客户结果
     * @apiVersion 4.0.0
     * @apiName  user_wechat/transfer_result
     * @apiGroup 后台-查询转移客户结果
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/transfer_result
     * @apiDescription 分配在职成员的客户 定时任务 1小时执行一次
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
    public function transfer_result(Request $request){

        $UserWechatServers = new UserWechatServers();
        $res=$UserWechatServers->transfer_result();
        if(!checkRes($res)){
            return error(0,$res);
        }

        return success();
    }
}
