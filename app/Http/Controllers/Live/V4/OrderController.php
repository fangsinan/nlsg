<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\BackendUser;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderController extends ControllerBackend
{
    public function index()
    {

    }

    /**
     * @api {get} api/live_v4/order/list 订单列表和详情
     * @apiVersion 4.0.0
     * @apiName  order/list
     * @apiGroup 直播后台-订单列表和详情
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/order/list
     * @apiDescription  订单列表和详情
     *
     * @apiParam {number} page 分页
     * @apiParam {number} size 条数
     * @apiParam {number} [id] 单条详情传id获取
     * @apiParam {strint} [ordernum] 订单编号
     * @apiParam {strint} [created_at] 订单时间范围(2020-01-01,2022-02-02)
     * @apiParam {strint} [pay_type] 支付渠道(1微信端 2app微信 3app支付宝 4ios)
     * @apiParam {strint} [os_type] 客户端(客户端:1安卓 2ios 3微信 )
     * @apiParam {strint} [phone] 账号
     * @apiParam {strint} [title] 直播标题
     * @apiParam {number=9,10,14,15,16} [type] 订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)
     *
     *
     * @apiSuccess {string[]} goods 商品信息
     * @apiSuccess {string[]} pay_record 支付信息
     * @apiSuccess {string[]} pay_record_detail 收益信息,当指定id时返回
     * @apiSuccess {string[]} live 所属直播信息
     * @apiSuccess {string[]} user 购买者信息
     * @apiSuccess {string} id 订单id
     * @apiSuccess {string} type 订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)
     * @apiSuccess {string} price 商品价格
     * @apiSuccess {string} pay_price 支付金额
     * @apiSuccess {string} status 支付状态  0 待支付  1已支付  2取消
     * @apiSuccess {string} pay_type 支付渠道
     * @apiSuccess {string} os_type 客户端
     * @apiSuccessExample  Success-Response:
     * [
     * {
     * "id": 167376,
     * "type": 10,
     * "relation_id": "0",
     * "pay_time": "2020-04-30 15:05:16",
     * "price": "99.00",
     * "user_id": 313125,
     * "pay_price": "99.00",
     * "pay_type": 0,
     * "ordernum": "202004301505044830",
     * "live_id": 17,
     * "os_type": 3,
     * "goods": {
     * "goods_id": 0,
     * "title": "数据错误",
     * "subtitle": "",
     * "cover_img": "",
     * "detail_img": "",
     * "price": "价格数据错误"
     * },
     * "pay_record": {
     * "ordernum": "202004301505044830",
     * "price": "99.00",
     * "type": 1,
     * "created_at": "2020-04-30 15:05:16"
     * },
     * "pay_record_detail": {
     * "id": 27001,
     * "type": 10,
     * "ordernum": "202004301505044830",
     * "user_id": 234586,
     * "user": {
     * "id": 234586,
     * "phone": "15305396370",
     * "nickname": "慧宇教育-王秀翠"
     * }
     * },
     * "live": {
     * "id": 17,
     * "title": "经营家庭和孩子的秘密——发现婚姻的小幸福，成就育儿的大智慧",
     * "describe": "王琨老师本人视频直播课，帮助你拥有幸福的婚姻、成为智慧的父母、培养优秀的孩子！",
     * "begin_at": "2021-01-21 19:00:00",
     * "cover_img": "/live/liveinfo30/20200121.png"
     * },
     * "user": {
     * "id": 313125,
     * "phone": "15042623555",
     * "nickname": "清然一平常心"
     * }
     * }
     * ]
     */
    public function list(Request $request)
    {
        $model = new Order();
        $data = $model->orderInLive($request->input(), $this->user);
        return $this->getRes($data);
    }

    public function listExcel(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        if (empty($user_id)) {
            exit();
        }
        $this->user = BackendUser::where('id', '=', $user_id)->first()->toArray();

        $columns = ['订单编号', '直播标题', '用户昵称', '用户手机', '商品', '类型',
            '商品价格', '支付价格', '推荐账户','推荐账户昵称', '支付状态', '支付方式', '下单时间', '订单来源'];
        $fileName = '直播商品列表' . date('Y-m-d H:i') . '.csv';
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

        $model = new Order();
//        $size = 500;
        $page = 1;
//        $request->offsetSet('size', $size);
//        $request->offsetSet('excel_flag', '1');
        $params = $request->all();
        $params['size'] = 500;
        $params['excel_flag'] = 1;

        $while_flag = true;
        while ($while_flag) {
//            $request->offsetSet('page', $page);
            $params['page'] = $page;
//            $list = $model->orderInLive($request->input(), $this->user);
            $list = $model->orderInLive($params, $this->user);
            $page++;
            if ($list->isEmpty()) {
                $while_flag = false;
            } else {
                foreach ($list as $v) {
                    $temp_v = [];
                    $temp_v['ordernum'] = '`' . ($v->ordernum ?? '');
                    $temp_v['title'] = $v->live->title ?? '';
                    $temp_v['nickname'] = $v->user->nickname ?? '';
                    $temp_v['phone'] = '`' . ($v->user->phone ?? '');
                    $temp_v['goods_name'] = $v->goods['title'] ?? '';

                    switch ((int)$v->type) {
                        case 9:
                            $temp_v['type'] = '精品课';
                            break;
                        case 10:
                            $temp_v['type'] = '直播';
                            break;
                        case 14:
                            $temp_v['type'] = '线下产品';
                            break;
                        case 15:
                            $temp_v['type'] = '讲座';
                            break;
                        case 16:
                            $temp_v['type'] = '会员';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }

                    $temp_v['g_p'] = $v->goods['price'] ?? '';
                    $temp_v['p_p'] = $v->pay_price ?? '';
                    $temp_v['t_p'] = $v->pay_record_detail->user->phone ?? '';
                    $temp_v['t_pn'] = $v->pay_record_detail->user->nickname ?? '';
                    switch ((int)$v->status) {
                        case 1:
                            $temp_v['p_status'] = '已支付';
                            break;
                        case 2:
                            $temp_v['p_status'] = '取消';
                            break;
                        case 0:
                            $temp_v['p_status'] = '待支付';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }
                    $temp_v['p_status'] = '已支付' ?? '';
                    switch (intval($v->pay_type)) {
                        case 1:
                            $temp_v['pay_type'] = '微信';
                            break;
                        case 2:
                            $temp_v['pay_type'] = '微信APP';
                            break;
                        case 3:
                            $temp_v['pay_type'] = '支付宝';
                            break;
                        case 4:
                            $temp_v['pay_type'] = '苹果';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }

                    $temp_v['time'] = $v->pay_time ?? '';
                    switch ((int)$v->os_type) {
                        case 1:
                            $temp_v['os'] = '安卓';
                            break;
                        case 2:
                            $temp_v['os'] = '苹果';
                            break;
                        case 3:
                            $temp_v['os'] = '微信';
                            break;
                        default:
                            $temp_v['type'] = '错误';

                    }
                    mb_convert_variables('GBK', 'UTF-8', $temp_v);
                    fputcsv($fp, $temp_v);
                    ob_flush();     //刷新输出缓冲到浏览器
                    flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
                }

            }
        }

        fclose($fp);
        exit();
    }

    public function inviterLiveList(Request $request)
    {
        $model = new Order();
        $temp_flag = $request->input('temp_flag',2);
        if ($temp_flag === 1){
            $data = $model->inviterLiveList($request->input(), $this->user);
        }else{
            $data = $model->inviterLiveListNew($request->input(), $this->user);
        }

        return $this->getRes($data);
    }

    public function inviterLiveListExcel(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        if (empty($user_id)) {
            exit();
        }
        $this->user = BackendUser::where('id', '=', $user_id)->first()->toArray();


        $request->offsetSet('excel_flag', '1');

        $columns = ['订单编号', '直播标题', '用户昵称', '用户手机', '商品', '类型',
            '商品价格', '支付价格', '源账户', '源直播', '源推荐账户', '支付状态', '支付方式', '下单时间', '订单来源'];
        $fileName = '直播销售列表' . date('Y-m-d H:i') . '.csv';

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

        $model = new Order();
        $size = 100;
        $page = 1;
        $request->offsetSet('size', $size);
        $request->offsetSet('excel_flag', '1');
        $while_flag = true;
        while ($while_flag) {
            $request->offsetSet('page', $page);
            $list = $model->inviterLiveList($request->input(), $this->user);
            if ($list->isEmpty()) {
                $while_flag = false;
            } else {
                foreach ($list as $v) {
                    $temp_v = [];
                    $temp_v['ordernum'] = '`' . ($v->ordernum ?? '');
                    $temp_v['title'] = $v->live->title ?? '';
                    $temp_v['nickname'] = $v->user->nickname ?? '';
                    $temp_v['phone'] = '`' . ($v->user->phone ?? '');
                    $temp_v['goods_name'] = $v->goods['title'] ?? '';

                    switch (intval($v->type)) {
                        case 9:
                            $temp_v['type'] = '精品课';
                            break;
                        case 10:
                            $temp_v['type'] = '直播';
                            break;
                        case 14:
                            $temp_v['type'] = '线下产品';
                            break;
                        case 15:
                            $temp_v['type'] = '讲座';
                            break;
                        case 16:
                            $temp_v['type'] = '会员';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }

                    $temp_v['g_p'] = $v->goods['price'] ?? '';
                    $temp_v['p_p'] = $v->pay_price ?? '';
                    $temp_v['t_p'] = $v->t_phone ?? '';
                    $temp_v['t_t'] = $v->t_title ?? '';
                    $temp_v['t_l'] = $v->live_phone ?? '';

                    switch (intval($v->status)) {
                        case 1:
                            $temp_v['p_status'] = '已支付';
                            break;
                        case 2:
                            $temp_v['p_status'] = '取消';
                            break;
                        case 0:
                            $temp_v['p_status'] = '待支付';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }
                    $temp_v['p_status'] = '已支付' ?? '';
                    switch (intval($v->pay_type)) {
                        case 1:
                            $temp_v['pay_type'] = '微信';
                            break;
                        case 2:
                            $temp_v['pay_type'] = '微信APP';
                            break;
                        case 3:
                            $temp_v['pay_type'] = '支付宝';
                            break;
                        case 4:
                            $temp_v['pay_type'] = '苹果';
                            break;
                        default:
                            $temp_v['type'] = '错误';
                    }

                    $temp_v['time'] = $v->pay_time ?? '';
                    switch (intval($v->os_type)) {
                        case 1:
                            $temp_v['os'] = '安卓';
                            break;
                        case 2:
                            $temp_v['os'] = '苹果';
                            break;
                        case 3:
                            $temp_v['os'] = '微信';
                            break;
                        default:
                            $temp_v['type'] = '错误';

                    }
                    mb_convert_variables('GBK', 'UTF-8', $temp_v);
                    fputcsv($fp, $temp_v);
                    ob_flush();     //刷新输出缓冲到浏览器
                    flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
                }
                $page++;
            }
        }

        fclose($fp);
        exit();
    }
}
