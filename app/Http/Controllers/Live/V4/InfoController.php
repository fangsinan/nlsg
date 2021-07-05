<?php


namespace App\Http\Controllers\Live\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\LiveInfoServers;
use Illuminate\Http\Request;

class InfoController extends ControllerBackend
{
    /**
     * @api {get} api/live_v4/live_info/live_sub_order 邀约
     * @apiVersion 4.0.0
     * @apiName  live_info/live_sub_order
     * @apiGroup 直播后台新增-邀约
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_sub_order
     * @apiDescription  邀约
     **/
    public function liveSubOrder(Request $request)
    {
        $excel_flag = $request->input('excel_flag', 0);
        $s = new LiveInfoServers();
        $data = $s->liveSubOrder($request->input());

        if (empty($excel_flag)) {
            return $this->getRes($data);
        } else {
            $columns = ['用户id', '用户账号', '用户昵称', '推客id', '推客账号', '推客昵称',
                '推客别名', '邀约时间', '直播id'];
            $fileName = '直播预约订单列表' . date('Y-m-d H:i') . '.csv';
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

            foreach ($data as $v) {
                $v = json_decode(json_encode($v), true);
                mb_convert_variables('GBK', 'UTF-8', $v);
                fputcsv($fp, $v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }
            fclose($fp);
            exit();
        }
    }

    /**
     * @api {get} api/live_v4/live_info/live_order 预约订单
     * @apiVersion 4.0.0
     * @apiName  live_info/live_order
     * @apiGroup 直播后台新增-预约订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_order
     * @apiDescription  预约订单
     **/
    public function liveOrder(Request $request)
    {
        $excel_flag = $request->input('excel_flag', 0);
        $s = new LiveInfoServers();
        $data = $s->liveOrder($request->input());
        if (empty($excel_flag)) {
            return $this->getRes($data);
        } else {
            $columns = ['用户id', '用户账号', '用户昵称', '推客id', '推客账号', '推客昵称',
                '推客别名', '支付价格', '支付时间', '直播id', '直播标题', '订单id'];
            $fileName = '直播预约订单列表' . date('Y-m-d H:i') . '.csv';
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

            foreach ($data as $v) {
                $v = json_decode(json_encode($v), true);
                mb_convert_variables('GBK', 'UTF-8', $v);
                fputcsv($fp, $v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }
            fclose($fp);
            exit();
        }

    }

    //评论
    public function comment(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->comment($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {get} api/live_v4/live_info/online_num 在线人数
     * @apiVersion 4.0.0
     * @apiName  live_info/online_num
     * @apiGroup 直播后台新增-在线人数
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num
     * @apiDescription  在线人数
     **/
    public function onlineNum(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->onlineNum($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {get} api/live_v4/live_info/online_num_info 在线人数详情
     * @apiVersion 4.0.0
     * @apiName  live_info/online_num_info
     * @apiGroup 直播后台新增-在线人数详情
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num_info
     * @apiDescription  在线人数详情
     **/
    public function onlineNumInfo(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->onlineNumInfo($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {get} api/live_v4/live_info/user_watch (未)进入直播间用户列表
     * @apiVersion 4.0.0
     * @apiName  live_info/user_watch
     * @apiGroup 直播后台新增-(未)进入直播间用户列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/user_watch
     * @apiDescription  (未)进入直播间用户列表
     **/
    public function userWatch(Request $request){
        $s = new LiveInfoServers();
        $data = $s->userWatch($request->input());
        return $this->getRes($data);
    }

}
