<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\Subscribe;
use App\Models\User;
use App\Servers\V5\TempLiveExcelServers;
use Illuminate\Http\Request;

class TempLiveExcelController extends ControllerBackend
{
    public function __construct() {
        parent::__construct();

        $temp_phone_list = ConfigModel::getData(66,1);
        $temp_phone_list   = preg_replace('/[^0-9]/i', ',', $temp_phone_list);
        $temp_phone_list = explode(',',$temp_phone_list);
        $temp_phone_list = array_filter($temp_phone_list);

        if (!in_array($this->user['username'],$temp_phone_list)){
            //抛出异常
            throw new \Exception('您没有权限');
        }
    }


    public function shouTingQingKuang(Request $request) {
        $list = (new TempLiveExcelServers())->shouTingQingKuang($request->input(),$this->user['id']);
        if (($list['code'] ?? '') === false) {
            return $this->getRes($list);
        }

        $columns  = [
            '订单ID', '订单号', '商户交易号', '购买直播间', '推荐人id', '金额', '支付时间',
            '用户手机号', '用户昵称', '企业微信标记', '用户注册时间', '企业微信昵称', '企业微信客服', '添加企业微信时间', '企业客服'
        ];
        $fileName = 'st-' . date('Y-m-d H:i:s') . '-' . rand(100, 999) . '.csv';
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
        foreach ($list as $v) {
            $v = (array)$v;
            mb_convert_variables('GBK', 'UTF-8', $v);
            fputcsv($fp, $v);
            ob_flush();     //刷新输出缓冲到浏览器
            flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
        }
        fclose($fp);
        exit();
    }


    public function weiJinZhiBo(Request $request) {

        $list = (new TempLiveExcelServers())->weiJinZhiBo($request->input(),$this->user['id']);

        if (($list['code'] ?? '') === false) {
            return $this->getRes($list);
        }

        $columns  = [
            '订单id', '订单号', '商户交易号', '购买直播间', '推荐人id', '金额', '支付时间', '用户手机号',
            '用户昵称', '企业微信标记', '用户注册时间', '企业微信昵称', '企业微信客服', '添加企业微信时间', '企业客服',
        ];
        $fileName = 'jr-' . date('Y-m-d H:i:s') . '-' . rand(100, 999) . '.csv';
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
        foreach ($list as $v) {
            $v = (array)$v;
            mb_convert_variables('GBK', 'UTF-8', $v);
            fputcsv($fp, $v);
            ob_flush();     //刷新输出缓冲到浏览器
            flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
        }
        fclose($fp);
        exit();

    }

    public function shouTingQingKuangFree(Request $request) {
        //直播间id   推荐人手机号  收听时间范围 是否观看
        $begin_time    = $request->input('begin_time', '');
        $end_time      = $request->input('end_time', '');
        $is_watch      = (int)($request->input('is_watch', 0));
        $live_id       = $request->input('live_id', 0);
        $twitter_phone = $request->input('twitter_phone', '');

        if (empty($live_id)) {
            return $this->getRes([
                'code' => false,
                'msg'  => '直播间id不能为空',
            ]);
        }

        if (empty($begin_time) || empty($end_time)) {
            return $this->getRes([
                'code' => false,
                'msg'  => '开始时间或结束时间不能为空',
            ]);
        }

        if (empty($twitter_phone)) {
            return $this->getRes([
                'code' => false,
                'msg'  => '推荐人手机号不能为空',
            ]);
        }

        $twitter_id = User::query()->where('phone', $twitter_phone)->value('id');

        $live_info = Live::query()->where('id', $live_id)->select(['id', 'title'])->first();

        $min_id = Subscribe::query()
            ->where('relation_id', $live_id)
            ->where('type', 3)
            ->where('status', 1)
            ->min('id');

        $max_id = Subscribe::query()
            ->where('relation_id', $live_id)
            ->where('type', 3)
            ->where('status', 1)
            ->max('id');

        $page       = 1;
        $size       = 10000;
        $while_flag = true;
        $server     = new TempLiveExcelServers();

        $columns  = [
            '直播id', '直播名称', '用户手机号', '用户昵称', '邀请时间',
        ];
        $fileName = 'free-jr-' . date('Y-m-d H:i:s') . '-' . rand(100, 999) . '.csv';
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
            if (!$twitter_id) {
                break;
            }

            $begin_id = ($page - 1) * $size + $min_id;
            $end_id   = $begin_id + $size;

            $list = $server->weiJinZhiBoFree([
                'live_id'    => $live_id,
                'twitter_id' => $twitter_id,
                'is_watch'   => $is_watch,
                'begin_time' => $begin_time,
                'end_time'   => $end_time,
                'begin_id'   => $begin_id,
                'end_id'     => $end_id,
            ],$this->user['id']);

            foreach ($list as $v) {
                $v = [
                    $live_id, $live_info->title,
                    $v->phone, $v->nickname, $v->created_at
                ];
                mb_convert_variables('GBK', 'UTF-8', $v);
                fputcsv($fp, $v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }

            $page++;
            if ($end_id > $max_id) {
                $while_flag = false;
            }
        }

        fclose($fp);
        exit();
    }

    public function qiYeWeiXin(Request $request) {
        $list = (new TempLiveExcelServers())->qiYeWeiXin($request->input(),$this->user['id']);
        if (($list['code'] ?? '') === false) {
            return $this->getRes($list);
        }

        $columns  = [
            '订单id', '订单号', '商户交易号', '购买直播间', '推荐人id', '金额', '支付时间', '用户手机号',
            '用户昵称', '企业微信标记', '用户注册时间', '企业微信昵称', '企业微信客服', '添加企业微信时间', '企业客服',
        ];
        $fileName = 'wx-' . date('Y-m-d H:i:s') . '-' . rand(100, 999) . '.csv';
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
        foreach ($list as $v) {
            $v = (array)$v;
            mb_convert_variables('GBK', 'UTF-8', $v);
            fputcsv($fp, $v);
            ob_flush();     //刷新输出缓冲到浏览器
            flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
        }
        fclose($fp);
        exit();
    }


}
