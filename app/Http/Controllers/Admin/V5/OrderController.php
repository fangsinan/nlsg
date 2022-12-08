<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\ZeroOrderListServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ControllerBackend
{
    public function zeroOrderList(Request $request): JsonResponse
    {
        return $this->getRes((new ZeroOrderListServers())->list($request->input(), $this->user));
    }

    public function zeroOrderListExcel(Request $request)
    {
        set_time_limit(600);
        $params         = $request->input();
        $params['size'] = 500;
        $page           = 1;
        $m              = new ZeroOrderListServers();

        $columns = [
            '订单编号', '用户昵称', '用户账号', '推荐人昵称', '推荐人账号', '预约时间',
            '直播信息', '来源直播信息', '是否添加微信'
        ];

        if ($this->user['role_id'] === 1){
            $columns[] = '企业微信账号';
            $columns[] = '企业微信名称';
        }

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

        while (true) {

            $params['page'] = $page;

            $temp_data = $m->list($params, $this->user, 1);
            if ($temp_data->isEmpty()) {
                break;
            }
            $page++;

            $temp_data = $temp_data->toArray();

            foreach ($temp_data as $v) {
                $temp_put_data                       = [];
                $temp_put_data['ordernum']           = ($v['ordernum'] ?? '-') . "\t";
                $temp_put_data['nickname']           = ($v['user']['nickname'] ?? '-') . "\t";
                $temp_put_data['phone']              = ($v['user']['phone'] ?? '-') . "\t";
                $temp_put_data['t_nickname']         = ($v['twitter']['nickname'] ?? '-') . "\t";
                $temp_put_data['t_phone']            = ($v['twitter']['phone'] ?? '-') . "\t";
                $temp_put_data['pay_time']           = $v['pay_time'] ?? '-';
                $temp_put_data['relation_title']     = $v['relation_live_info']['title'] ?? '-';
                $temp_put_data['live_title']         = $v['from_live_info']['title'] ?? '-';
                $temp_put_data['is_wechat']          = $v['is_wechat'] === 1 ? '未加' : '已加';

                if ($this->user['role_id'] === 1){
                    $temp_put_data['follow_user_userid'] = ($v['admin_wechat']->follow_user_userid ?? '-') . "\t";
                    $temp_put_data['follow_user']        = ($v['admin_wechat']->name ?? '-') . "\t";
                }

                mb_convert_variables('GBK', 'UTF-8', $temp_put_data);
                fputcsv($fp, $temp_put_data);
                ob_flush();
                flush();
            }
        }

        fclose($fp);
        exit();


    }

}
