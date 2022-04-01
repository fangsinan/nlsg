<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\ConfigModel;
use App\Models\OfflineProducts;
use App\Models\User;
use App\Models\UserWechat;
use App\Models\UserWechatName;
use App\Models\UserWechatTransferLog;
use App\Models\UserWechatTransferRecord;
use App\Servers\UserWechatServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserWechatController extends ControllerBackend
{



    public function test(){
        $UserWechatServers = new UserWechatServers();

        $UserWechatServers->set_wechat_user_id();
//        $res=$UserWechatServers->get_user_info('wok8dJEQAAdOT37rRpX-CUZCuFYPWt5Q');
//        $res=\App\Http\Controllers\Api\V4\UserWechat::getUserDetail(['wok8dJEQAAdOT37rRpX-CUZCuFYPWt5Q'],$UserWechatServers->token);
//        var_dump($res);
    }


    /**
     * @api {get} api/admin_v4/user_wechat/get_offline_products 获取线上训练营
     * @apiVersion 4.0.0
     * @apiName  user_wechat/get_offline_products
     * @apiGroup 后台-微信客户管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/get_offline_products
     * @apiDescription 获取线上训练营
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
    public function get_offline_products(){

        $config=ConfigModel::getData(65, 1);
        if($config){
            $config=json_decode($config,true);
        }
        return success($config);

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
     * @apiParam {string} is_shill 是否退款 0否 1是
     * @apiParam {string} offline_id 线下产品id
     * @apiParam {string} name 客户名称
     * @apiParam {string} phone 手机号
     * @apiParam {string} start_time 开始时间
     * @apiParam {string} end_time 结束时间
     * @apiParam {string} follow_user_userid 跟进客户员工userid
     * @apiParam {string} source_follow_user_userid 来源的客户员工id
     * @apiParam {string} transfer_status  接替状态， 1-接替完毕 2-等待接替 3-客户拒绝 4-接替成员客户达到上限 5-无接替记录
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

//        DB::connection()->enableQueryLog();

        $query = UserWechat::with(
            [
                'user_orders:id,user_id,relation_id,ordernum,pay_price,is_shill','user_orders.offline:id,title,subtitle',
                'user:id,nickname,phone,unionid',
                'follow_staff:id,qw_name,follow_user_userid',
                'source_staff:id,qw_name,follow_user_userid',
            ])

//            ->where('unionid','<>','')
            ->when(!empty($params['name']), function ($query) use ($params) {
                $query->where('name', $params['name']);
            })

            ->when(isset($params['transfer_status']), function ($query) use ($params) {
                $query->where('transfer_status', $params['transfer_status']);
            })


            ->when(!empty($params['is_shill']), function ($query) use ($params) {
                $query->whereHas('user_orders', function ($query) use ($params) {
                    $query->where('is_shill',  $params['is_shill'] );
                });
            })

            ->when(!empty($params['offline_id']), function ($query) use ($params) {
                $query->whereHas('user_orders', function ($query) use ($params) {
                    $query->where('relation_id',  $params['offline_id'] );
                });
            })

            ->when(!empty($params['start_time']) && !empty($params['end_time']), function ($query) use ($params) {
                $query->whereBetween('follow_user_createtime',[strtotime($params['start_time']),strtotime($params['end_time'])]);
            })

            ->when(!empty($params['phone']), function ($query) use ($params) {
                $query->whereHas('user', function ($query) use ($params) {
                    $query->where('phone',  $params['phone'] );
                });
            })

            ->when(!empty($params['follow_user_userid']), function ($query) use ($params) {
                $query->whereHas('follow_staff', function ($query) use ($params) {
                    $query->where('follow_user_userid',  $params['follow_user_userid'] );
                });
            })
            ->when(!empty($params['source_follow_user_userid']), function ($query) use ($params) {
                $query->whereHas('source_staff', function ($query) use ($params) {
                    $query->where('follow_user_userid',  $params['source_follow_user_userid'] );
                });
            });

        $lists = $query->orderBy('id','desc')->paginate(get_page_size($params))->toArray();

//        $logs = \Illuminate\Support\Facades\DB::getQueryLog();
//        dd($logs);

        foreach ($lists['data'] as &$val){
            $val['follow_user_createtime'] =date('Y-m-d H:i:s',$val['follow_user_createtime']);
        }

        return success($lists);
    }


    /**
     * @api {get} api/admin_v4/user_wechat/search_wechat_staff_user_list 获取员工列表
     * @apiVersion 4.0.0
     * @apiName  user_wechat/search_wechat_staff_user_list
     * @apiGroup 后台-微信客户管理
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

        $lists = $query->paginate(get_page_size($params))->toArray();

        return success($lists);
    }


    /**
     * @api {get} api/admin_v4/user_wechat/get_wechat_staff_user_list 获取员工列表
     * @apiVersion 4.0.0
     * @apiName  user_wechat/get_wechat_staff_user_list
     * @apiGroup 后台-微信客户管理
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
     * @apiGroup 后台-微信客户管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/transfer_customer
     * @apiDescription 分配在职成员的客户
     *
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
//        $data=[
//            'handover_userid'=>'DongYue',
//            'takeover_userid'=>'SunXia',
//            'userids'=>'137728,137984,138240',
//        ];
        $res=$UserWechatServers->transfer_customer($data);
        if(!checkRes($res)){
            return error(0,$res);
        }

        return success();
    }


    /**
     * @api {get} api/admin_v4/user_wechat/transfer_result 查询转移客户结果
     * @apiVersion 4.0.0
     * @apiName  user_wechat/transfer_result
     * @apiGroup 后台-微信客户管理
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

    /**
     * @api {get} api/admin_v4/user_wechat/search_transfer_record 客户转移记录
     * @apiVersion 4.0.0
     * @apiName  user_wechat/search_transfer_record
     * @apiGroup 后台-微信客户管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/search_transfer_record
     * @apiDescription 客户转移记录
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *          "status":"1已完成 2接替中",
     *          "total":"总条数",
     *          "finish_total":"完成转移数量",
     *          "wait_total":"等待转移数量",
     *    }
     * }
     */
    public function search_transfer_record(Request $request){

        $params= $request->input();

        $query = UserWechatTransferRecord::with(
            [
                'handover_user:id,qw_name,follow_user_userid',
                'takeover_user:id,qw_name,follow_user_userid',
            ])
            ->when(!empty($params['status']), function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when(!empty($params['handover_userid']), function ($query) use ($params) {
                $query->where('handover_userid', $params['handover_userid']);
            })
            ->when(!empty($params['takeover_userid']), function ($query) use ($params) {
                $query->where('takeover_userid', $params['takeover_userid']);
            });

        $lists = $query->orderBy('id','desc')->paginate(10)->toArray();

        return success($lists);
    }

    /**
     * @api {get} api/admin_v4/user_wechat/search_transfer_log 客户转移日志
     * @apiVersion 4.0.0
     * @apiName  user_wechat/search_transfer_log
     * @apiGroup 后台-客户转移日志
     * @apiParam transfer_record_id 转移记录ID
     * @apiParam user_wechat_id 用户ID
     * @apiParam handover_userid 原添加成员的id
     * @apiParam takeover_userid 接替成员的id
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/user_wechat/search_transfer_log
     * @apiDescription 客户转移记录
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
    public function search_transfer_log(Request $request){
        $params= $request->input();

        $query = UserWechatTransferLog::with(
            [
                'handover_user:id,qw_name,follow_user_userid',
                'takeover_user:id,qw_name,follow_user_userid',
            ])

            ->when(!empty($params['transfer_record_id']), function ($query) use ($params) {
                $query->where('transfer_record_id', $params['transfer_record_id']);
            })
            ->when(!empty($params['user_wechat_id']), function ($query) use ($params) {
                $query->where('user_wechat_id', $params['user_wechat_id']);
            })
            ->when(!empty($params['status']), function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when(!empty($params['handover_userid']), function ($query) use ($params) {
                $query->where('handover_userid', $params['handover_userid']);
            })
            ->when(!empty($params['takeover_userid']), function ($query) use ($params) {
                $query->where('takeover_userid', $params['takeover_userid']);
            });

        $lists = $query->orderBy('id','desc')->paginate(10)->toArray();

        return success($lists);
    }
}
