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
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_sub_order
     * @apiDescription  邀约
     * @apiParam {number} live_id 直播id
     *
     * @apiParam {string} [excel_flag=1,0] 是否未导出请求(1是)
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [user_id] 用户id
     * @apiParam {string} [phone] 用户u账号
     * @apiParam {string} [t_nickname] 推荐昵称
     * @apiParam {string} [t_user_id] 推荐用户id
     * @apiParam {string} [t_phone] 推荐账号
     * @apiParam {string} [son_flag] 别名
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
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_order
     * @apiDescription  预约订单
     * @apiParam {number} live_id 直播id
     *
     * @apiParam {string} [excel_flag=1,0] 是否未导出请求(1是)
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [phone] 用户u账号
     * @apiParam {string} [t_nickname] 推荐昵称
     * @apiParam {string} [t_phone] 推荐账号
     * @apiParam {string} [son_flag] 别名
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
            $fileName = '直播间预约下单列表' . date('Y-m-d H:i') . '.csv';
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
     * @api {get} api/live_v4/live_info/live_order_kun 王琨直播间的成交数据
     * @apiVersion 4.0.0
     * @apiName  live_info/live_order_kun
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_order_kun
     * @apiDescription  王琨直播间的成交数据
     * @apiParam {number} live_id 直播id
     *
     * @apiParam {string} [excel_flag=1,0] 是否未导出请求(1是)
     * @apiParam {string} [ordernum] 订单编号
     * @apiParam {string} [phone] 用户账号
     * @apiParam {string} [invite_phone] 推荐人手机号
     * @apiParam {string} [protect_phone] 保护人手机号
     * @apiParam {string} [diamond_phone] 别名
     * @apiParam {number=1,2,3} [qd] 渠道(1抖音 2李婷 3自有平台)
     **/
    public function liveOrderKun(Request $request)
    {
        $excel_flag = $request->input('excel_flag', 0);
        $s = new LiveInfoServers();
        $data = $s->liveOrderKun($request->input());
        if (empty($excel_flag)) {
            return $this->getRes($data);
        } else {
            $columns = ['订单编号', '支付金额', '数量', '支付时间', '类型名称', '购买人账号', '购买人昵称', '购买人身份',
                '推荐人账号', '推荐人昵称', '关系保护账号', '关系保护昵称', '关系保护身份', '受益人id', '受益人金额',
                '是否抖音渠道', '抖音订单号', '渠道类型', '渠道名称',
                '用户第一次购买直播间id', '用户第一次购买直播间金额', '用户第一次购买直播间时间', '是否退款'];
            $fileName = '直播间订单列表' . date('Y-m-d H:i') . '.csv';
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
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num
     * @apiDescription  在线人数
     * @apiParam {number} live_id 直播id
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
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num_info
     * @apiDescription  在线人数详情
     * @apiParam {number} live_id 直播id
     * @apiParam {string} date 时间,精确到分钟(2021-01-01 10:00:01)
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
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/user_watch
     * @apiDescription  (未)进入直播间用户列表
     * @apiParam {number} live_id 直播id
     * @apiParam {number=1,2} flag 标记,1是进入了,2是没进入
     * @apiParam {string} [excel_flag=1,0] 是否未导出请求(1是)
     **/
    public function userWatch(Request $request)
    {
        $excel_flag = $request->input('excel_flag', 0);
        $s = new LiveInfoServers();
        $data = $s->userWatch($request->input());
        if (empty($excel_flag)) {
            return $this->getRes($data);
        } else {
            $columns = ['编号', '用户id', '用户账号', '时间', '邀约人', '邀约人别名'];
            $fileName = '直播间用户观看分析' . date('Y-m-d H:i') . '.csv';
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
     * @api {get} api/live_v4/live_info/statistics 统计数据
     * @apiVersion 4.0.0
     * @apiName  live_info/statistics
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/statistics
     * @apiDescription  统计数据
     * @apiParam {number} live_id 直播id
     **/
    public function statistics(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->statistics($request->input());
        return $this->getRes($data);
    }

}
