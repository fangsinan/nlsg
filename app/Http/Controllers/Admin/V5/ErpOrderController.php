<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\DouDianServers;
use App\Servers\V5\erpOrderServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ErpOrderController extends ControllerBackend
{

    //虚拟订单需要发货的列表
    public function erpOrderList(Request $request): JsonResponse {
        return $this->getRes((new erpOrderServers())->list($request->input()));
    }


    public function bindAddress(Request $request): JsonResponse {
        return $this->getRes((new erpOrderServers())->bindAddress($request->input()));
    }

    public function addRefundOrder(Request $request): JsonResponse {
        return $this->getRes((new erpOrderServers())->addRefundOrder($request->input(),$this->user['id'] ?? 0));
    }

    public function erpOrderListExcel(Request $request) {
        set_time_limit(600);
        $params         = $request->input();
        $params['size'] = 500;

        $eos        = new erpOrderServers();
        $while_flag = true;
        $page       = 1;

//        订单ID、订单编号、商品信息、数量、金额、支付时间、发货状态（待发货、已发货）、用户信息（姓名、手机号）、收货地址、退款状态（未退款、退款中、已退款）

        $columns  = [
            '订单ID', '订单编号', '商品信息', '数量', '金额', '支付时间', '账号', '发货状态', '收货人', '收货人电话', '收货地址', '退款状态',
        ];
        $fileName = date('Y-m-d H:i') . '-' . rand(100, 999) . '.csv';
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

        while ($while_flag) {
            $params['page'] = $page;

            $temp_data = $eos->list($params, 1);

            if ($temp_data->isEmpty()) {
                $while_flag = false;
            } else {
                $temp_data = $temp_data->toArray();

                foreach ($temp_data as $v) {

                    $temp_put_data                = [];
                    $temp_put_data['id']          = $v['id'];
                    $temp_put_data['ordernum']    = '`' . $v['ordernum'];
                    $temp_put_data['goods_title'] = $v['textbook_info']['title'];
                    $temp_put_data['goods_num']   = $v['live_num'];
                    $temp_put_data['pay_price']   = $v['pay_price'];
                    $temp_put_data['pay_time']    = $v['pay_time'];
                    $temp_put_data['username']    = $v['user']['phone'];

                    switch ($v['send_status']) {
                        case 1:
                            $temp_put_data['send_status'] = '未发货';
                            break;
                        case 2:
                            $temp_put_data['send_status'] = '已发货';
                            break;
                        default:
                            $temp_put_data['send_status'] = '错误';
                    }

                    $temp_put_data['address_name']   = $v['address_name'];
                    $temp_put_data['address_phone']  = '`' . $v['address_phone'];
                    $temp_put_data['address_detail'] = $v['address_detail'];
                    if ($v['shill_status'] === 1) {
                        $temp_put_data['shill_status'] = '未退款';
                    } else {
                        $temp_put_data['shill_status'] = '已退款';
                    }

                    mb_convert_variables('GBK', 'UTF-8', $temp_put_data);
                    fputcsv($fp, $temp_put_data);
                    ob_flush();     //刷新输出缓冲到浏览器
                    flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
                }
            }
            $page++;
        }
        fclose($fp);
        exit();
    }
    
}
