<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Live;
use App\Models\MallAddress;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\User;
use App\Servers\LiveInfoServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscribeController extends ControllerBackend {


    public function liveSelect(Request $request) {

        $query = Live::query();
        //非超管角色可看live
        $live_id_role = IndexController::getLiveRoleIdList($this->user);
        if ($live_id_role !== null) {
            if ($live_id_role === []) {
                return success([]);
            }
            $query->whereIn('id', $live_id_role);
        }

        if ($this->user['live_role'] !== 0) {
            $query->where('id', '>', 51);
        }

        $lists = $query->where('status', 4)
            ->where('is_del', 0)
            ->select('id', 'user_id', 'title')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return success(['data' => $lists]);
    }

    public function indexExcel(Request $request) {
        set_time_limit(600);
        $flag = true;
        $page = 1;
        $size = 5000;

        $columns  = [
            '订单编号', '直播标题', '用户昵称', '用户账号', '收件人', '收件人电话', '地址', '直播定价', '分销金额', '分销昵称',
            '分销账号', '订单来源', '支付时间', '支付金额', '支付方式', '创建时间'
        ];
        $fileName = date('Y-m-d H:i') . '-' . rand(10, 99) . '.csv';
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
        while ($flag) {
            $list = $this->index($request, 1, $size, $page);
            foreach ($list as $v) {
                $v      = json_decode(json_encode($v), true);
                $temp_v = [];
                if (empty($v['order']['ordernum'] ?? '')) {
                    $temp_v['ordernum'] = '-';
                } else {
                    $temp_v['ordernum'] = '`' . $v['order']['ordernum'];
                }
                $temp_v['live_title'] = $v['live']['title'] ?? '-';
                $temp_v['nickname']   = $v['user']['nickname'] ?? '-';
                if (empty($v['user']['phone'] ?? '')) {
                    $temp_v['phone'] = '-';
                } else {
                    $temp_v['phone'] = '`' . $v['user']['phone'];
                }

                if (empty($v['user']['address'])) {
                    $temp_v['address_name']  = '';
                    $temp_v['address_phone'] = '';
                    $temp_v['address']       = '';
                } else {
                    $temp_v['address_name']  = $v['user']['address']['name'];
                    $temp_v['address_phone'] = '`' . $v['user']['address']['phone'];
                    $temp_v['address']       = $v['user']['address']['province_name'] . ' ' .
                        $v['user']['address']['city_name'] . ' ' .
                        $v['user']['address']['area_name'] . ' ' .
                        $v['user']['address']['details'];
                }

                $temp_v['live_price'] = $v['live']['price'] ?? '-';
                $temp_v['t_price']    = $v['live']['twitter_money'] ?? '-';
                $temp_v['t_nickname'] = $v['twitter']['nickname'] ?? '-';
                $temp_v['t_phone']    = $v['twitter']['phone'] ?? '-';
                switch (intval($v['order']['os_type'] ?? 0)) {
                    case 1:
                        $temp_v['os_type'] = '安卓';
                        break;
                    case 2:
                        $temp_v['os_type'] = 'ios';
                        break;
                    case 3:
                        $temp_v['os_type'] = '微信';
                        break;
                    default:
                        $temp_v['os_type'] = '-';
                }
                $temp_v['pay_time']  = $v['order']['pay_time'] ?? '-';
                $temp_v['pay_price'] = $v['order']['pay_price'] ?? '-';
                switch (intval($v['order']['pay_type'] ?? 0)) {
                    case 1:
                        $temp_v['pay_type'] = '微信';
                        break;
                    case 2:
                        $temp_v['pay_type'] = 'app微信';
                        break;
                    case 3:
                        $temp_v['pay_type'] = '支付宝';
                        break;
                    case 4:
                        $temp_v['pay_type'] = 'ios';
                        break;
                    default:
                        $temp_v['pay_type'] = '-';
                }
                $temp_v['created_at'] = $v['created_at'] ?? '-';
                mb_convert_variables('GBK', 'UTF-8', $temp_v);
                fputcsv($fp, $temp_v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }

            $page++;
            if (count($list) < $size) {
                $flag = false;
            }
        }

        fclose($fp);
        exit();
    }

    /**
     * @api {get} api/live_v4/sub/index 预约列表
     * @apiVersion 4.0.0
     * @apiName  sub/index
     * @apiGroup 直播后台-评论列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/sub/index
     * @apiDescription  预约列表
     *
     * @apiParam {number} page      分页
     * @apiParam {string} ordernum  订单号
     * @apiParam {string} title     直播标题
     * @apiParam {string} phone     用户账号
     * @apiParam {string} twitter_phone     推客账号
     * @apiParam {string} date      支付时间
     * @apiParam {string} created_at    下单时间
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

    public function index(Request $request, $get_excel = 0, $excel_size = 10, $excel_page = 1) {
        $title         = $request->get('title', '');
        $ordernum      = $request->get('ordernum', '');
        $phone         = $request->get('phone', '');
        $date          = $request->get('date', '');
        $created_at    = $request->get('created_at', '');
        $now_date      = date('Y-m-d H:i:s');
        $twitter_phone = $request->input('twitter_phone', '');
        $page          = $request->input('page', 1);
        $size          = $request->input('size', 10);
        $type          = $request->input('type', '');
        $live_id       = (int)$request->get('live_id', 0);

        //1、查询是否有搜索手机号或 id
        //2、2~3个表查数据
        //3、组合最后的各个查询数据
        if (!empty($phone)) {
            $phoneUser = User::query()->select('id')->where('phone', $phone)->first();
        }
        if (!empty($twitter_phone)) {//推客的id
            $twitter_phoneUser = User::query()->select('id')->where('phone', $twitter_phone)->first();
        }

        $live_query = Live::query()->where('status', 4);
        $live_query->where('app_project_type','=',APP_PROJECT_TYPE);
        if (!empty($live_id)) {   //管理员看全部
            $live_query->where('id', $live_id);
        }

        $live_query->select("id", "title", "price", "twitter_money", "is_free");

        //非超管角色可看live
        if (!empty($title)) {
            $live_query->where('title', 'like', '%' . $title . '%');
        }

        //非超管角色可看live
        $live_id_role = IndexController::getLiveRoleIdList($this->user);

        if ($live_id_role !== null) {
            if ($live_id_role === []) {
                return success([]);
            }
            $live_data = $live_query->whereIn('id', $live_id_role)->get()->toArray();
        } else {
            $live_data = $live_query->get()->toArray();
        }

        $live_ids = array_column($live_data, "id");

        //处理直播数据
        $new_live_data = [];
        if (!empty($live_data)) {
            foreach ($live_data as $live_key => $live_val) {
                $new_live_data[$live_val['id']] = $live_val;
            }
        }

        $subObj   = new Subscribe();
        $orderObj = new Order();

        $order_flag = 0;
        if ($live_id !== 0 && $live_data[0]['is_free'] === 1) {//免费 不需要查order表
            $query = DB::table($subObj->getTable() . ' as sub');
        } else {//付费的情况下 用order表驱动sub表
            $order_flag = 1;
            $query      = DB::table($orderObj->getTable() . ' as order');
            $query->leftJoin($subObj->getTable() . ' as sub', 'order.id', '=', 'sub.order_id');
            $query->where('order.type', 10);  //多加一个筛选
        }

        $query->whereIn('sub.relation_id', $live_ids)
            ->where('sub.type', 3)
            ->where('sub.status', 1);
        if ($live_id !== 0 && $live_data[0]['is_free'] === 0) {
            $query->where('sub.order_id', '>', 0);
        }

//        $this->user['role_id'] = 13;
//        $this->user['username'] = '18512347777';

        //按渠道过滤
        if ($this->user['role_id'] === 1) {
            $twitter_id_list = null;
        } else {
            $liServers       = new LiveInfoServers();
            $twitter_id_list = $liServers->twitterIdList($this->user['username']);
        }

        if ($twitter_id_list !== null) {
            $query->whereIn('sub.twitter_id', $twitter_id_list);
        }

        // 加在此处  如有搜索条件 可以走到联合索引
        if (!empty($phoneUser)) {
            $query->where('sub.user_id', $phoneUser['id']);
        }
        if (!empty($phone) && empty($phoneUser)){
            $query->where('sub.user_id','=',0);
        }

        if ($this->user['role_id'] === 13 && $this->user['live_role_button'] === 2) {
            $query->where('sub.order_id', '>', 0);
        }

        if (!empty($ordernum)) {
            if ($live_id === 0 || $live_data[0]['is_free'] === 0) {
                $query->where('order.ordernum', $ordernum);
            }
        }

        if (!empty($twitter_phoneUser)) {
            if ($this->user['username'] === '13522223779') {
                $query->where('sub.twitter_id', $twitter_phoneUser['id']);
            } else if ($live_id === 0 || $live_data[0]['is_free'] === 0) {
                $query->where('order.twitter_id', $twitter_phoneUser['id']);
            }
        }
        $query->where('sub.is_del', 0);
        //支付时间
        if (!empty($created_at)) {
            $query->where('sub.created_at', '>=', $created_at[0]);
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            }
            $query->where('sub.created_at', '<', $created_at[1]);
        }
        if ($type === "count") {
            $lists = $query->count("sub.id");
            return success(['total' => $lists]);
        }

        $filed = [
            'sub.id', 'sub.type', 'sub.user_id', 'sub.relation_id', 'sub.pay_time', 'sub.order_id', 'sub.created_at', 'sub.twitter_id',
            'order.ordernum as order_ordernum', 'order.pay_price as order_pay_price', 'order.pay_time as order_pay_time', 'order.twitter_id as order_twitter_id', 'order.pay_type as order_pay_type', 'order.os_type as order_os_type', 'order.created_at as order_created_at'
        ];
        if ($order_flag === 0) {
            $filed = ['sub.id', 'sub.type', 'sub.user_id', 'sub.relation_id', 'sub.pay_time', 'sub.order_id', 'sub.created_at', 'sub.twitter_id'];
        }
        $query->select($filed)->orderBy('sub.created_at', 'desc');

        if ($get_excel) {
            $excel_offset  = ($excel_page - 1) * $excel_size;
            $lists['data'] = $query->limit($excel_size)->offset($excel_offset)->get();
            if ($lists['data']->isEmpty()) {
                $lists['data'] = [];
            } else {
                $lists['data'] = $lists['data']->toArray();
            }
        } else {
            $lists['data'] = $query->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        }

        $ordernum = [];
        $user_ids = [];

        //转下数组 方便后续处理
        $lists = json_decode(json_encode($lists), true);
        /*************    处理推客信息   *************/
        //  将 twitter_id、ordernum、user_id 取出来  单独查询处理
        $twitter_get_ids = [];
        foreach ($lists['data'] as &$t_val) {

            //dd($t_val);
            // order 数据
            if (empty($t_val['order_id'])) {
                $t_val['order'] = [];
            } else {
                $t_val['order'] = [
                    "id"         => $t_val['order_id'] ?? 0,
                    "ordernum"   => $t_val['order_ordernum'] ?? '-',
                    "pay_price"  => $t_val['order_pay_price'] ?? 0,
                    "pay_time"   => $t_val['order_pay_time'] ?? '-',
                    "twitter_id" => $t_val['order_twitter_id'] ?? 0,
                    "pay_type"   => $t_val['order_pay_type'] ?? 0,
                    "os_type"    => $t_val['order_os_type'] ?? '-',
                    "created_at" => $t_val['order_created_at'] ?? '-',
                ];
            }

            // live 数据
            $t_val['live']    = $new_live_data[$t_val['relation_id']];
            $t_val['twitter'] = [];
            $twitter_id       = $t_val['order']['twitter_id'] ?? 0;
            //免费的邀约人是live_count_down
            if ($t_val['live']['is_free'] === 1) {
                $twitter_id = $t_val['twitter_id'];
            }

            //推客id需要单独记录
            if (!empty($twitter_id)) {
                $t_val['twitter']['twitter_id'] = $twitter_id;
                $twitter_get_ids[]              = $twitter_id;
            }

            //ordernum
            if (!empty($t_val['order']['ordernum'])) {
                $ordernum[] = (string)$t_val['order']['ordernum'];

            }

            if (!empty($t_val['user_id'])) {
                $user_ids[] = $t_val['user_id'];
            }
        }
        $get_twitters = User::select("id", "phone", "nickname")->whereIn('id', $twitter_get_ids)->get()->toArray();
        $get_twitter  = [];
        foreach ($get_twitters as $twitter_v) {
            $get_twitter[$twitter_v['id']] = $twitter_v;
        }
        /*************    处理推客信息end   *************/

        //查询剩余信息
        //'user:id,nickname,phone',
        //'order.pay_record_detail:id,type,ordernum,user_id,price',
        //'order.pay_record_detail.user:id,phone,nickname',
        $new_detailes = [];
        $detailes     = PayRecordDetail::with(['user:id,phone,nickname'])
            ->select('id', 'type', 'ordernum', 'user_id', 'price')
            ->whereIn('ordernum', $ordernum)->get()->toArray();

        foreach ($detailes as $dk => $dv) {
            $new_detailes[$dv['ordernum']] = $dv;
        }

        // 用户信息
        $new_users = [];
        $users     = User::select('id', 'nickname', 'phone')->whereIn('id', $user_ids)->get()->toArray();


        $maModel = new MallAddress();
        foreach ($users as $dk => $dv) {
            $new_users[$dv['id']] = $dv;
//            if ($get_excel) {
////                $new_users[$dv['id']]['address'] = ($maModel->getList($dv['id'], 0, 1))[0] ?? '';
//            }
        }

        foreach ($lists['data'] as &$val) {

            // 将推客信息  合并到数据
            if (!empty($val['twitter']['twitter_id'])) {
                $val['twitter'] = $get_twitter[$val['twitter']['twitter_id']] ?? [];
            }

            // 将订单信息  合并到数据
            if (!empty($val['order']) && !empty($new_detailes[$val['order']['ordernum']])) {
                $val['order']['pay_record_detail'] = $new_detailes[$val['order']['ordernum']] ?? [];
            }

            // 将用户信息  合并到数据
//            if (!empty($new_users[$val['user_id']])) {
            $val['user'] = $new_users[$val['user_id']] ?? [];
//            }

        }
        if ($get_excel) {
            return $lists['data'];
        }

        return success($lists);
    }

}
