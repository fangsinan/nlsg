<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\DouDianDataServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DouDianController extends ControllerBackend
{
    public function orderList(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->list($request->input()));
    }

    //下拉商品数据
//    public function selectGoodsList(): JsonResponse {
//        return $this->getRes((new DouDianDataServers())->selectGoodsList());
//    }

    //下拉订单状态
    public function selectOrderStatus(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->selectOrderStatus($request->input()));
    }


    public function orderDecryptQuota(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->orderDecryptQuota($request->input()));
    }

    public function orderDecryptQuotaReset(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->orderDecryptQuotaReset($request->input()));
    }

    public function orderListExcel(Request $request) {
        set_time_limit(600);
        $params         = $request->input();
        $params['size'] = 100;
        $s              = new DouDianDataServers();

        $while_flag = true;
        $page       = 1;

        $columns = [
            '抖音订单编号', '订单状态', '支付时间', '订单完成时间', '订单创建时间',
            '订单金额', '支付金额',
            '收件人电话', '收件人姓名', '收件人地址', '收件人详细地址','收件人完整地址',
            '解密进度', '解密错误信息', '商品名称','购买数量'
        ];

        $fileName = 'dou_dian_' . date('Y-m-d H:i') . '-' . rand(100, 999) . '.csv';
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

            $temp_data = $s->list($params, 1);

            if ($temp_data->isEmpty()) {
                $while_flag = false;
            } else {
                $temp_data = $temp_data->toArray();

                foreach ($temp_data as $v) {
                    foreach ($v['order_list'] as $vv) {

                        $temp_put_data                      = [];
                        $temp_put_data['order_id']          = $v['order_id']."\t";
                        $temp_put_data['order_status_desc'] = $v['order_status_desc'];
                        $temp_put_data['pay_time_date']     = $v['pay_time_date'];
                        $temp_put_data['finish_time_date']  = $v['finish_time_date'];
                        $temp_put_data['create_time_date']  = $v['create_time_date'];
                        $temp_put_data['order_amount_yuan'] = $v['order_amount_yuan'];
                        $temp_put_data['pay_amount_yuan']   = $v['pay_amount_yuan'];
                        $temp_put_data['post_tel']          = $v['post_tel']."\t";
                        $temp_put_data['post_receiver']     = $v['post_receiver'];
                        $temp_put_data['post_addr']         = $v['post_addr_province_name'] . $v['post_addr_city_name'] . $v['post_addr_town_name'];
                        $temp_put_data['post_addr_detail']  = $v['post_addr_detail'];
                        $temp_put_data['post_addr_full']    =  $temp_put_data['post_addr'] . $temp_put_data['post_addr_detail'];
                        switch ($v['decrypt_step']) {
                            case 0:
                                $temp_put_data['decrypt_step_desc'] = '未解密';
                                break;
                            case 1:
                                $temp_put_data['decrypt_step_desc'] = '收件人电话解密';
                                break;
                            case 2:
                                $temp_put_data['decrypt_step_desc'] = '收件人电话,姓名解密';
                                break;
                            case 3:
                                $temp_put_data['decrypt_step_desc'] = '解密完毕';
                                break;
                            case 4:
                                $temp_put_data['decrypt_step_desc'] = '解密完毕并添加用户';
                                break;
                            default:
                                $temp_put_data['decrypt_step_desc'] = '解密失败';
                        }

                        $temp_put_data['decrypt_err_msg'] = $v['decrypt_err_msg'];
                        $temp_put_data['product_name']    = $vv['product_info']['name'] ?? '-';
                        $temp_put_data['item_num'] = $vv['item_num'] ?? '-';

                        mb_convert_variables('GBK', 'UTF-8', $temp_put_data);
                        fputcsv($fp, $temp_put_data);
                        ob_flush();
                        flush();
                    }
                }

            }

            $page++;
        }
        fclose($fp);
        exit();

    }

}
