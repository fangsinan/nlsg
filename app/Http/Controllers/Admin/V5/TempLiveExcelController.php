<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\TempLiveExcelServers;
use Illuminate\Http\Request;

class TempLiveExcelController extends ControllerBackend
{

    public function shouTingQingKuang(Request $request) {

        $list = (new TempLiveExcelServers())->shouTingQingKuang($request->input());
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

        $list = (new TempLiveExcelServers())->weiJinZhiBo($request->input());

        if (($list['code'] ?? '') === false) {
            return $this->getRes($list);
        }

        $columns = [
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

    public function qiYeWeiXin(Request $request) {
        $list = (new TempLiveExcelServers())->qiYeWeiXin($request->input());
        if (($list['code'] ?? '') === false) {
            return $this->getRes($list);
        }

        return $this->getRes($list);
    }


}
