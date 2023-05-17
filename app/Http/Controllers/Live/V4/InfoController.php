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
    public function liveSubOrder(Request $request) {
        $request->input('excel_flag', 0);
        $s = new LiveInfoServers();
//        $data = $s->liveSubOrder($request->input(),$this->user);
        $data = $s->liveSubOrderNew($request->input(),$this->user);
        return $this->getRes($data);
    }

    public function liveSubOrderExcel(Request $request)
    {
        set_time_limit(240);
        $columns = [
            '预约id','用户id', '用户账号', '用户昵称', '推客id', '推客账号',
            '推客昵称', '推客别名', '邀约时间', '直播id','客服姓名','客服账号'
        ];
        $fileName = date('Y-m-d H:i') . '-' . random_int(10, 99) . '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'ab');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中

        $s = new LiveInfoServers();

        $request->offsetSet('size', 10000);
        $page = 1;
        $while_flag = true;

        while ($while_flag) {
            $request->offsetSet('page', $page);
            $data = $s->liveSubOrderNew($request->input(),$this->user);
            $page++;
            if (empty($data)) {
                $while_flag = false;
            }else{
                foreach ($data as $v) {
                    $v = json_decode(json_encode($v), true);
                    mb_convert_variables('GBK', 'UTF-8', $v);
                    fputcsv($fp, $v);
                }
            }
            ob_flush();     //刷新输出缓冲到浏览器
            flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
        }

        fclose($fp);
        exit();
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
        $data = $s->liveOrder($request->input(),$this->user);
        if (empty($excel_flag)) {
            return $this->getRes($data);
        }
        $columns = ['用户id', '用户账号', '用户昵称', '推客id', '推客账号', '推客昵称',
            '推客别名', '支付价格', '支付时间', '直播id', '直播标题', '订单id'];
//            $fileName = '直播间预约下单列表' . date('Y-m-d H:i') . '.csv';
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

    public function liveOrderExcel(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->liveOrder($request->input(),$this->user);
        if (($data['code'] ?? true) === false) {
            exit($data['msg']);
        }
        $columns = ['订单编号', '用户id', '用户账号', '用户昵称', '推客id', '推客账号', '推客昵称',
            '推客别名', '支付价格', '支付时间', '直播id', '直播标题', '源直播id', '源直播标题',
                    '收货地址','收货人电话','收货人','企业微信客服名称','企业微信客服电话',
            ];
//        $fileName = '直播间预约下单列表' . date('Y-m-d H:i') . '.csv';
        $fileName = date('Y-m-d H:i') . '-' . random_int(10, 99) . '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'ab');//打开output流
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
        $s = new LiveInfoServers();
        $data = $s->liveOrderKun($request->input(),$this->user);
        return $this->getRes($data);
    }

    //http://127.0.0.1:8000/api/live_v4/live_info/live_order_kun_excel?live_id=130&excel_flag=1
    public function liveOrderKunExcel(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->liveOrderKun($request->input(),$this->user);
        if (($data['code'] ?? true) === false) {
            exit($data['msg']);
        }
//        if(!empty($data)){
//            $UserId_Arr=[];
//            foreach ($data as $k=>$v) {
//                $UserId_Arr[]=$v->user_id;
//            }
//            $query = DB::table('nlsg_user as U')
//                ->select(['U.id','U.unionid','N.qw_name','W.follow_user_userid'])
//                ->leftjoin('nlsg_user_wechat as W',function($query){
//                    $query->on('W.unionid','=','U.unionid')->where('W.unionid','<>','');
//                })
//                ->leftJoin('nlsg_user_wechat_name as N','N.follow_user_userid','=','W.follow_user_userid')
//                ->whereIn('U.id', $UserId_Arr);
//            $UserArr=$query->get()->toArray();
//            $UnionArr=[];
//            foreach ($UserArr as $key=>$val){
//                $UnionArr[$val->id]=[
//                    'qw_name'=>(empty($val->qw_name))?'':$val->qw_name,
//                    'follow_user_userid'=>(empty($val->follow_user_userid))?'':$val->follow_user_userid
//                    ];
//            }
//            foreach ($data as $k=>$v){
//                $v->unionid=$UnionArr[$v->user_id]['qw_name'];
//                $v->follow_user_userid=$UnionArr[$v->user_id]['follow_user_userid'];
//            }
//        }
        $columns = ['订单编号', '支付金额', '数量', '支付时间', '类型名称',
            '购买人账号', '购买人昵称', '购买人id', '购买人身份',
            '推荐人账号', '推荐人昵称', '关系保护id', '关系保护账号', '关系保护昵称', '关系保护身份',
            '受益人id', '受益人金额',
            '钻石合伙人id', '钻石合伙人账号', '钻石合伙人昵称', '钻石合伙人身份',
            '是否抖音渠道', '抖音订单号', '抖音下单时间', '渠道类型', '渠道名称',
            '首次购买直播间','讲师', '标题',
            '首次金额', '首次购买时间', '是否退款',
            '微信客服', '客服编号',
            '收货地址'
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
     * @apiParam {number} [son_id] 渠道过滤(son_flag对应的son_id)
     * @apiSuccess {string[]} son_flag 渠道列表,如果有,则显示过滤选项
     * @apiSuccess {string[]} list 内容
     **/
    public function onlineNum(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->onlineNum($request->input(), $this->user);
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

    public function onlineNumInfoExcel(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->onlineNumInfo($request->input());
        if (($data['code'] ?? true) === false) {
            exit($data['msg']);
        }
        $columns = ['用户id', '用户账号', '推荐人id', '推荐人手机号', '推荐人昵称', '预约时间'];
//        $fileName = '在线人数分析' . date('Y-m-d H:i') . '.csv';
        $fileName = date('Y-m-d H:i') . '-' . rand(10, 99) . '.csv';

        header('Content-Description: File Transfer');
        header('Cache-Control: max-age=0');
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
//            $fileName = '直播间用户观看分析' . date('Y-m-d H:i') . '.csv';
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

    public function userWatchExcel(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->userWatch($request->input());
        if (($data['code'] ?? true) === false) {
            exit($data['msg']);
        }
        $columns = ['编号', '用户id', '用户账号', '时间', '邀约人', '邀约人别名'];
//        $fileName = '直播间用户观看分析' . date('Y-m-d H:i') . '.csv';
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

    /**
     * @api {get} api/live_v4/live_info/statistics 统计数据
     * @apiVersion 4.0.0
     * @apiName  live_info/statistics
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/statistics
     * @apiDescription  统计数据
     * @apiParam {number} live_id 直播id
     *
     * @apiSuccess {string} headimg 头像,
     * @apiSuccess {string} nickname 老师,
     * @apiSuccess {string} user_id 老师id,
     * @apiSuccess {string} begin_at 直播开始,
     * @apiSuccess {string} end_at 直播结束,
     * @apiSuccess {string} live_login 人气,
     * @apiSuccess {string} order_num 总预约人数
     * @apiSuccess {string} watch_counts 观看人数
     * @apiSuccess {string} not_watch_counts 未观看人数,
     * @apiSuccess {string} total_order 成交单数
     * @apiSuccess {string} total_order_money":总金额
     * @apiSuccess {string} total_order_user 购买人数
     * @apiSuccess {string} total_sub_count 总预约人数
     * @apiSuccess {string} total_not_buy 为购买人数
     * @apiSuccess {string} more_than_30m 大于30分钟
     * @apiSuccess {string} more_than_60m 小于30分钟
     * @apiSuccess {string} total_login 累计人次
     * @apiSuccess {string} total_sub 累计人数
     **/
    public function statistics(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->statistics($request->input(),$this->user);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/live_v4/live_info/flag_poster_list 海报列表
     * @apiVersion 4.0.0
     * @apiName  live_info/flag_poster_list
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/flag_poster_list
     * @apiDescription  海报列表
     * @apiParam {number} live_id 直播id
     *
     * @apiSuccess {string} id id
     * @apiSuccess {string} live_id 直播id
     * @apiSuccess {string} son_id 渠道用户id
     * @apiSuccess {string} status 状态(待开启  2开启  3关闭)
     * @apiSuccess {string} son_flag 渠道账号
     **/
    public function flagPosterList(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->flagPosterList($request->input(), $this->user);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/live_v4/live_info/flag_poster_status 海报状态修改
     * @apiVersion 4.0.0
     * @apiName  live_info/flag_poster_status
     * @apiGroup 直播后台新增
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live_info/flag_poster_status
     * @apiDescription  海报状态修改
     * @apiParam {number} id id
     * @apiParam {string=on,off,del} flag 动作
     **/
    public function flagPosterStatus(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->flagPosterStatus($request->input());
        return $this->getRes($data);
    }


}
