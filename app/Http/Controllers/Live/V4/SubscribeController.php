<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\BackendLiveRole;
use App\Models\MallAddress;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class SubscribeController extends ControllerBackend
{

    public function indexExcel(Request $request)
    {
        set_time_limit(600);
        $flag = true;
        $page = 1;
        $size = 50;

        $columns = ['订单编号', '直播标题', '用户昵称', '用户账号','收件人','收件人电话','地址', '直播定价', '分销金额', '分销昵称',
            '分销账号', '订单来源', '支付时间', '支付金额', '支付方式', '创建时间'];
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
                $v = json_decode(json_encode($v), true);
                $temp_v = [];
                if (empty($v['order']['ordernum'] ?? '')) {
                    $temp_v['ordernum'] = '-';
                } else {
                    $temp_v['ordernum'] = '`' . $v['order']['ordernum'];
                }
                $temp_v['live_title'] = $v['live']['title'] ?? '-';
                $temp_v['nickname'] = $v['user']['nickname'] ?? '-';
                if (empty($v['user']['phone'] ?? '')) {
                    $temp_v['phone'] = '-';
                } else {
                    $temp_v['phone'] = '`' . $v['user']['phone'];
                }

                if (empty($v['user']['address'])){
                    $temp_v['address_name'] = '';
                    $temp_v['address_phone'] = '';
                    $temp_v['address'] = '';
                }else{
                    $temp_v['address_name'] = $v['user']['address']['name'];
                    $temp_v['address_phone'] = '`'.$v['user']['address']['phone'];
                    $temp_v['address'] = $v['user']['address']['province_name'].' '.
                        $v['user']['address']['city_name'].' '.
                        $v['user']['address']['area_name'].' '.
                        $v['user']['address']['details'];
                }

                $temp_v['live_price'] = $v['live']['price'] ?? '-';
                $temp_v['t_price'] = $v['live']['twitter_money'] ?? '-';
                $temp_v['t_nickname'] = $v['twitter']['nickname'] ?? '-';
                $temp_v['t_phone'] = $v['twitter']['phone'] ?? '-';
                switch (intval($v['order']['os_type'])) {
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
                $temp_v['pay_time'] = $v['order']['pay_time'] ?? '-';
                $temp_v['pay_price'] = $v['order']['pay_price'] ?? '-';
                switch (intval($v['order']['pay_type'])) {
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
                $temp_v['created_at'] = $v['order']['created_at'] ?? '-';

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
    public function index(Request $request, $get_excel = 0, $excel_size = 10, $excel_page = 1)
    {
        $title = $request->get('title') ?? '';
        $ordernum = $request->get('ordernum') ?? '';
        $phone = $request->get('phone') ?? '';
        $date = $request->get('date') ?? '';
        $created_at = $request->get('created_at') ?? '';
        $now_date = date('Y-m-d H:i:s');
        $twitter_phone = $request->input('twitter_phone', '');


        //1、查询是否有搜索手机号或 id
        //2、2~3个表查数据
        //3、组合最后的各个查询数据
        if (!empty($phone)) {
            $phoneUser = User::select('id')->where('phone', $phone)->first();
        }
        if (!empty($twitter_phone)) {//推客的id
            $twitter_phoneUser = User::select('id')->where('phone', $twitter_phone)->first();
        }

        $query = Subscribe::with([
            //'user:id,nickname,phone',
            'live:id,title,price,twitter_money,is_free',
            //'order.pay_record_detail:id,type,ordernum,user_id,price',
            //'order.pay_record_detail.user:id,phone,nickname',
            'order:id,ordernum,pay_price,pay_time,twitter_id,pay_type,os_type,created_at'
        ]);

        if ($this->user['role_id'] == 13 && $this->user['live_role_button'] == 2) {
            $query->has('order');
        }

        if ($this->user['live_role'] == 21) {
            $live_user_id = $this->user['user_id'];
            $query->whereHas('live', function ($q) use ($live_user_id) {
                $q->where('user_id', '=', $live_user_id)->where('id', '>', 52);
            });
        } elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $query->whereHas('live', function ($q) use ($son_user_id) {
                $q->whereIn('user_id', $son_user_id)->where('id', '>', 52);
            });
        }

        if (!empty($title)) {
            $query->whereHas('live', function ($q) use ($title) {
                $q->where('title', 'like', '%' . $title . '%');
            });
        }
        if (!empty($ordernum)) {
            $query->whereHas('order', function ($q) use ($ordernum) {
                $q->where('ordernum', $ordernum);
            });
        }
        if (!empty($twitter_phoneUser)) {
            if ($this->user['username'] == 13522223779) {
                $query->where('twitter_id', $twitter_phoneUser['id']);
            } else {
                $query->whereHas('order', function ($q) use ($twitter_phoneUser) {
                    $q->where('twitter_id', $twitter_phoneUser['id']);
                });
            }

        }

        if (!empty($date)) {
            $query->whereHas('order', function ($q) use ($date, $now_date) {
                $q->where('pay_time', '>=', $date[0]);
                if (empty($date[1] ?? '')) {
                    $date[1] = $now_date;
                }
                $q->where('pay_time', '<', $date[1]);
            });
        }

        $query->select('id', 'type', 'user_id', 'relation_id', 'pay_time',
            'order_id', 'created_at', 'twitter_id')
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('type', 3);

        //sub创建时间
        if (!empty($created_at)) {
            $query->where('created_at', '>=', $created_at[0]);
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            }
            $query->where('created_at', '<', $created_at[1]);
        }


        if (!empty($phoneUser)) {
            $query->where('user_id', $phoneUser['id']);
        }


        $query->orderBy('created_at', 'desc');

        if ($get_excel) {
            $excel_offset = ($excel_page - 1) * $excel_size;
            $lists['data'] = $query->limit($excel_size)->offset($excel_offset)->get();
            if ($lists['data']->isEmpty()) {
                $lists['data'] = [];
            } else {
                $lists['data'] = $lists['data']->toArray();
            }
        } else {
            $lists = $query->paginate(10)->toArray();
        }

        $ordernum = [];
        $user_ids = [];
        foreach ($lists['data'] as &$val) {
            $val['twitter'] = [];
            $twitter_id = $val['order']['twitter_id'] ?? 0;
            //免费的邀约人是live_count_down
            if ($val['live']['is_free'] == 1) {
                $twitter_id = $val['twitter_id'];
            }

            if (!empty($twitter_id)) {
                $twitter = User::find($twitter_id);
                $val['twitter']['phone'] = $twitter['phone'];
                $val['twitter']['nickname'] = $twitter['nickname'];
            }

            //ordernum
            if (!empty($val['order']['ordernum'])) {
                $ordernum[] = $val['order']['ordernum'];

            }

            if (!empty($val['user_id'])) {
                $user_ids[] = $val['user_id'];
            }

        }

        //查询剩余信息
        //'user:id,nickname,phone',
        //'order.pay_record_detail:id,type,ordernum,user_id,price',
        //'order.pay_record_detail.user:id,phone,nickname',
        $new_detailes = [];
        $detailes = PayRecordDetail::with(['user:id,phone,nickname',])
            ->select('id', 'type', 'ordernum', 'user_id', 'price')
            ->whereIn('ordernum', $ordernum)->get()->toArray();
        foreach ($detailes as $dk => $dv) {
            $new_detailes[$dv['ordernum']] = $dv;
        }
        $new_users = [];
        $users = User::select('id', 'nickname', 'phone')->whereIn('id', $user_ids)->get()->toArray();

        $maModel = new MallAddress();
        foreach ($users as $dk => $dv) {
            $new_users[$dv['id']] = $dv;
            $new_users[$dv['id']]['address'] = ($maModel->getList($dv['id'], 0, 1))[0] ?? '';
        }

        foreach ($lists['data'] as &$val) {

            if (!empty($val['order']) && !empty($new_detailes[$val['order']['ordernum']])) {
                $val['order']['pay_record_detail'] = $new_detailes[$val['order']['ordernum']];
            }

            if (!empty($new_users[$val['user_id']])) {
                $val['user'] = $new_users[$val['user_id']];
            }

        }

        if ($get_excel) {
            return $lists['data'];
        } else {
            return success($lists);
        }


//        $query = Subscribe::with([
//            'user:id,nickname,phone',
//            'live:id,title,price,twitter_money',
//            'order.pay_record_detail:id,type,ordernum,user_id,price',
//            'order.pay_record_detail.user:id,phone,nickname',
//            'order:id,ordernum,pay_price,pay_time,twitter_id,pay_type,os_type,created_at'
//        ]);
//
//        if (!empty($twitter_phone)){
//            $query->whereHas('order.pay_record_detail.user',function($q)use($twitter_phone){
//                $q->where('phone','like',"%$twitter_phone%");
//            });
//        }
//
//        if($this->user['live_role'] == 21){
//            $live_user_id = $this->user['user_id'];
//            $query->whereHas('live',function($q)use($live_user_id){
//                $q->where('user_id','=',$live_user_id);
//            });
//        }elseif ($this->user['live_role'] == 23) {
//            $blrModel = new BackendLiveRole();
//            $son_user_id = $blrModel->getDataUserId($this->user['username']);
//            $query->whereHas('live', function ($q) use ($son_user_id) {
//                $q->whereIn('user_id', $son_user_id);
//            });
//        }
//
//        if(!empty($phone)){
//            $query->whereHas('user', function ($q) use($phone){
//                $q->where('phone', $phone);
//            });
//        }
//        if(!empty($title)){
//            $query->whereHas('live', function ($q) use($title){
//                $q->where('title', 'like', '%'.$title.'%');
//            });
//        }
//        if(!empty($ordernum)){
//            $query->whereHas('order', function ($q) use($ordernum){
//                $q->where('ordernum', $ordernum);
//            });
//        }
//        if(!empty($date)){
//            $query->whereHas('order', function ($q) use($date,$now_date){
////                $date = explode(',', $date);
//                $q->where('pay_time','>=', $date[0]);
//                if (empty($date[1] ?? '')) {
//                    $date[1] = $now_date;
//                }
//                $q->where('pay_time','<', $date[1]);
//            });
//        }
////        if(!empty($created_at)){
////            $query->whereHas('order', function ($q) use($created_at,$now_date){
////                $created_at = explode(',', $created_at);
////                $q->where('pay_time','>=', $created_at[0]);
////                if (empty($created_at[1] ?? '')) {
////                    $created_at[1] = $now_date;
////                }
////                $q->where('pay_time','<', $created_at[1]);
////            });
////        }
//
//        $query->select('id', 'type', 'user_id', 'relation_id', 'pay_time','order_id','created_at')
//            ->where('is_del',0)
//            ->where('status',1)
//            ->where('type',3);
//
//
//        //sub创建时间
//        if(!empty($created_at)){
////            $created_at = explode(',', $created_at);
//            $query->where('created_at','>=', $created_at[0]);
//            if (empty($created_at[1] ?? '')) {
//                $created_at[1] = $now_date;
//            }
//            $query->where('created_at','<', $created_at[1]);
//        }
//        $lists = $query->orderBy('created_at', 'desc')
//            ->paginate(10)
//            ->toArray();
//
//        foreach ($lists['data'] as &$val){
//            $val['twitter'] = [];
//            if(!empty($val['order']['twitter_id'])){
//                $twitter = User::find($val['order']['twitter_id']);
//                $val['twitter']['phone'] = $twitter['phone'];
//                $val['twitter']['nickname'] = $twitter['nickname'];
//            }
//        }
//
//
//        return success($lists);

    }
}
