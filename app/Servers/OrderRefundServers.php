<?php

namespace App\Servers;

use App\Imports\UsersImport;
use App\Models\Order;
use App\Models\OrderRefundExcel;
use App\Models\OrderRefundLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class OrderRefundServers
{
    public function add($params, $admin_id)
    {
        $file_name = $params['file_name'] ?? '';
        $url = $params['url'] ?? '';

//        $file_name = '1111group/20210926订单退款表格实例.xlsx';
//        $url = 'http://image.nlsgapp.com/1111group/20210926订单退款表格实例.xlsx';

        if (empty($file_name) || empty($url)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        try {
            $file = 'order_refund_' . time() . random_int(1000, 9999) . '.xlsx';
        } catch (\Exception $e) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);//在需要用户检测的网页里需要增加下面两行
        $content = curl_exec($ch);

        if (empty($content)) {
            return ['code' => false, 'msg' => '文件数据错误'];
        }

        Storage::put($file, $content);

        $check_file = Storage::exists($file);

        if (!$check_file) {
            return ['code' => false, 'msg' => '文件不存在'];
        }

        $excel_data = Excel::toArray(new UsersImport, base_path() . '/storage/app/' . $file);

        Storage::delete($file);

        $excel_data = $excel_data[0] ?? [];
        if (empty($excel_data)) {
            return ['code' => false, 'msg' => '数据错误:表1'];
        }

        $title = array_shift($excel_data);

        if ([$title[0], $title[1]] !== ["商户订单", "退款金额"]) {
            return ['code' => false, 'msg' => '表格结构错误("商户订单","退款金额")'];
        }

        //存入excel
        $oreModel = new OrderRefundExcel();
        $oreModel->file_name = $file_name;
        $oreModel->url = $url;
        $oreModel->admin_id = $admin_id;
        $ore_res = $oreModel->save();
        if ($ore_res === false) {
            return ['code' => false, 'msg' => '表格记录失败,请重试'];
        }

        //存入log
        $add_data = [];
        $temp_order_list = [];

        foreach ($excel_data as $v) {
            $temp_add_data = [];
            $temp_add_data['status'] = 1;
//            if (!is_int($v[0])){
//                return ['code'=>false,'msg'=>'请确保商户订单数据格式正确'];
//            }
            $temp_add_data['ordernum'] = $v[0];
//            $temp_add_data['ordernum'] = substr($temp_add_data['ordernum'],0,-1);
            $temp_add_data['check_price'] = (float)($v[1] ?? 0);
            if (empty($temp_add_data['ordernum'])) {
                $temp_add_data['status'] = 4;
            }
            if (in_array($temp_add_data['ordernum'], $temp_order_list, true)) {
//                $temp_add_data['status'] = 2;
                continue;
            }
            if (empty($temp_add_data['check_price'])) {
                $temp_add_data['status'] = 5;
            }

            if ($temp_add_data['status'] === 1) {
                $temp_order_list[] = $temp_add_data['ordernum'];
            }
            $temp_add_data['excel_id'] = $oreModel->id;
            $temp_add_data['admin_id'] = $admin_id;

            $add_data[] = $temp_add_data;
        }

        if (!empty($add_data)) {
            $res = DB::table('nlsg_order_refund_log')->insert($add_data);
            if ($res === false) {
                return ['code' => false, 'msg' => '添加失败'];
            }
        }

        //初步过滤数据
//        DB::table('nlsg_order_refund_log')
//            ->where('excel_id','=',$ore_res->id)
//            ->where('check_price','=',0)
//            ->update([
//                'status'=>5
//            ]);

        $edit_list = DB::table('nlsg_order_refund_log as rl')
            ->leftJoin('nlsg_order as o', 'rl.ordernum', '=', 'o.ordernum')
            ->where('rl.status', '=', 1)
//            ->where('rl.excel_id','=',$oreModel->id)
            ->select(['rl.id', 'rl.ordernum', 'rl.check_price', 'o.id as oid', 'o.status',
                'o.is_shill', 'o.shill_job_price', 'o.is_refund', 'o.pay_price'])
            ->get();

//dd($edit_list->toArray());

//1:已登记  4订单状态错误 5退款金额错误 10退款中(发起退款)   20已完成(校验完成)
        if ($edit_list->isNotEmpty()) {
            foreach ($edit_list as $v) {
                $check_order_flag = true;

                $temp_orlModel = OrderRefundLog::find($v->id);
                if ($v->oid === null) {
                    $check_order_flag = false;
                    $temp_orlModel->status = 4;
                } else {
                    if ($v->pay_price !== $v->check_price) {
                        $check_order_flag = false;
                        $temp_orlModel->status = 5;
                    }
                    if ($v->is_refund === 2 || $v->is_refund === 3) {
                        $temp_orlModel->status = 20;
                    }
                }

                $temp_orlModel->save();

                //如果数据没有错误,则修改订单表执行退款
                if ($check_order_flag) {
                    $temp_oModel = Order::find($v->oid);
                    $temp_oModel->is_shill = 1;
                    $temp_oModel->save();
                }

            }
        }


        return ['code' => true, 'msg' => '添加成功'];

    }

    public function list($params, $admin_id)
    {
        $size = $params['size'] ?? 10;
        $ordernum = $params['ordernum'] ?? '';
        $status = $params['status'] ?? 0;

        $query = OrderRefundLog::query();
        if (!empty($ordernum)) {
            $query->where('ordernum', 'like', "%$ordernum%");
        }
        if (!empty($status)) {
            $query->where('status', '=', $status);
        }

        $query->orderBy('excel_id', 'desc')->orderBy('id');
        $query->select(['id', 'ordernum', 'check_price', 'created_at', 'status']);

        //1:已登记  4订单状态错误 5退款金额错误 10退款中(发起退款)   20已完成(校验完成)

        $res = $query->paginate($size);

        foreach ($res as $v) {
            switch ($v->status) {
                case 1:
                    $v->status_name = '已登记';
                    break;
                case 4:
                    $v->status_name = '订单状态错误';
                    break;
                case 5:
                    $v->status_name = '退款金额错误';
                    break;
                case 10:
                    $v->status_name = '退款中';
                    break;
                case 20:
                    $v->status_name = '已完成';
                    break;
                default:
                    $v->status_name = '-';
            }
        }

        $custom = collect(['status' => [
            1 => '已登记',
            4 => '订单状态错误',
            5 => '退款金额错误',
            10 => '退款中',
            20 => '已完成'
        ]]);

        return $custom->merge($res);
    }

}
