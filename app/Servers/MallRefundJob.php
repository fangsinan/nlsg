<?php


namespace App\Servers;


use App\Models\GetPriceTools;
use App\Models\MallRefundRecord;
use App\Models\RunRefundRecord;
use Illuminate\Support\Str;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;

class MallRefundJob
{
    public function mallRefundCheck()
    {
        $list = MallRefundRecord::from('nlsg_mall_refund_record as mrr')
            ->join('nlsg_mall_order as mo', 'mrr.order_id', '=', 'mo.id')
            ->join('nlsg_pay_record as pr', 'pr.ordernum', '=', 'mo.ordernum')
            ->where('mrr.run_refund', '=', 2)
            ->where('pr.order_type', '=', 10)
            ->limit(100)
            ->select(['mrr.id as service_id', 'service_num', 'mrr.order_id',
                'mrr.order_detail_id', 'mrr.type', 'mrr.pay_type',
                'mrr.status as service_status', 'mrr.user_id', 'pr.transaction_id',
                'pr.ordernum', 'pr.price as all_price', 'mrr.price as refund_price'])
            ->get();

        ini_set('date.timezone', 'Asia/Shanghai');

        foreach ($list as $v) {
            switch ($v->pay_type) {
                case 1:
                    //微信公众号
                    $this->weChatRefundCheck($v, 1);
                    break;
                case 2:
                    //微信app
                    $this->weChatRefundCheck($v, 2);
                    break;
                case 3:
                    //支付宝app
                    $this->aliRefundCheckGrace($v);
                    break;
            }
        }
    }

    public function mallRefund()
    {
        $list = MallRefundRecord::from('nlsg_mall_refund_record as mrr')
            ->join('nlsg_mall_order as mo', 'mrr.order_id', '=', 'mo.id')
            ->join('nlsg_pay_record as pr', 'pr.ordernum', '=', 'mo.ordernum')
            ->where('mrr.run_refund', '=', 1)
            ->where('pr.order_type', '=', 10)
            ->limit(100)
            ->select(['mrr.id as service_id', 'service_num', 'mrr.order_id',
                'mrr.order_detail_id', 'mrr.type', 'mrr.pay_type',
                'mrr.status as service_status', 'mrr.user_id', 'pr.transaction_id',
                'pr.ordernum', 'pr.price as all_price', 'mrr.price as refund_price'])
            ->get();

        ini_set('date.timezone', 'Asia/Shanghai');

        foreach ($list as $v) {
            switch ($v->pay_type) {
                case 1:
                    //微信公众号
                    $this->weChatRefund($v, 1);
                    break;
                case 2:
                    //微信app
                    $this->weChatRefund($v, 2);
                    break;
                case 3:
                    //支付宝app
//                    $this->aliRefund($v);
                    $this->aliRefundGrace($v);
                    break;
            }
        }
    }

    public function weChatRefundCheck($v, $flag)
    {
        if ($flag == 1) {
            $config = Config('wechat.payment.wx_wechat');
        } else {
            $config = Config('wechat.payment.default');
        }
        $data = array(
            'appid' => $config['app_id'], //公众账号ID
            'mch_id' => $config['mch_id'], //商户号
            'nonce_str' => \Illuminate\Support\Str::random(16), //随机字符串
            'out_refund_no' => $v->service_num, //商户退款单号
            'refund_fee' => intval(GetPriceTools::PriceCalc('*', $v->refund_price, 100)),
            'total_fee' => intval(GetPriceTools::PriceCalc('*', $v->all_price, 100)), //订单金额
            'transaction_id' => $v->transaction_id, //微信订单号
        );
        $data['sign'] = self::sign_data($data, $config['key']); //加密串

        $xml = self::ToXml($data); //数据包拼接
        $res = self::postXmlCurl($config['refund_url'], $xml, 2);
        libxml_disable_entity_loader(true);
        if (!$res) {
            return true;
        }
        try {
            $xml = simplexml_load_string($res, 'SimpleXMLElement',
                LIBXML_NOCDATA);
            $xml = json_decode(json_encode($xml), true);

            if (isset($xml['result_code']) && $xml['result_code'] == 'SUCCESS') {
                $refund_fee = $xml['refund_fee']; //退款金额
                $this->toChange($refund_fee, $v);
            }
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function aliRefundCheck($v)
    {
        require_once base_path() . '/vendor/alipay-sdk/aop/AopClient.php';
        require_once base_path() . '/vendor/alipay-sdk/aop/request/AlipayTradeFastpayRefundQueryRequest.php';
        $aop = new \AopClient();
        $aop->appId = env('ALI_APP_ID');
        $aop->alipayrsaPublicKey = env('ALI_PUBLIC_KEY');
        $aop->rsaPrivateKey = env('ALI_PRIVATE_KEY2');
        $aop->gatewayUrl = env('ALI_PAYMENT_REFUND_CHECK_URL');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayTradeFastpayRefundQueryRequest();
        $out_request_no = $v->service_num;
        $trade_no = $v->transaction_id;

        $request->setBizContent("{" .
            "\"trade_no\":\"$trade_no\"," .
            "\"out_trade_no\":\"\"," .
            "\"out_request_no\":\"$out_request_no\"" .
            "}");

        try {
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_",
                    $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                $refund_amount = $result->$responseNode->refund_amount; //退款金额
                $this->toChange($refund_amount, $v);
            }
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function aliRefundCheckGrace($v)
    {
        $config = Config('pay.alipay');
        $alipay = Pay::alipay($config);
        $order = [
            'out_trade_no' => $v->ordernum,
            'out_request_no' => $v->service_num
        ];
        $result = $alipay->find($order, 'refund');

        if ($result->code == 10000) {
            $this->toChange($v->price, $v);
        }
    }

    /**
     * 退款成功后的操作
     * @param $id
     * @param $fee
     * @param $order
     */
    public function toChange($fee, $order)
    {
        if ($order->pay_type !== 3) {
            $fee = GetPriceTools::PriceCalc('/', $fee, 100);
        }

        $now_date = date('Y-m-d H:i:s');

        //修改售后表
        $mrr = MallRefundRecord::find($order->service_id);
        $mrr->status = 60;
        $mrr->succeed_at = $now_date;
        $mrr->run_refund = 3;
        $mrr->refund_fee = $fee;
        $mrr->save();

    }

    //优雅的支付宝退款
    public function aliRefundGrace($v)
    {
        $config = Config('pay.alipay');
        $alipay = Pay::alipay($config);
        $order = [
            'out_trade_no' => $v->ordernum,
            'refund_amount' => $v->refund_price,
            'out_request_no' => $v->service_num,
        ];

        $now_date = date('Y-m-d H:i:s');
        $rrrModel = new RunRefundRecord();
        $rrrModel->order_type = 1;
        $rrrModel->order_id = $v->service_id;

        try {
            $result = $alipay->refund($order);
            if ($result->code == 10000) {
                $mrr = MallRefundRecord::find($v->service_id);
                $mrr->status = 50;
                $mrr->refund_sub_at = $now_date;
                $mrr->run_refund = 2;
                $mrr->save();
                $rrrModel->is_success = 1;
                $rrrModel->refund_money = $result->refund_fee;
            } else {
                $rrrModel->is_success = 2;
                $rrrModel->error_code = '';
                $rrrModel->error_msg = '';
            }
        } catch (\Exception $e) {
            $rrrModel->is_success = 2;
            $rrrModel->error_code = $e->getCode();
            $rrrModel->error_msg = substr($e->getMessage() ?? '', 0, 1000);
        }
        $rrrModel->save();
        return true;
    }

    /**
     * 微信退款
     * @param $v
     * @param $flag
     * @return bool
     */
    public function weChatRefund($v, $flag)
    {
        if ($flag == 1) {
            //h5
            $config = Config('wechat.payment.wx_wechat');
        } else {
            //微信app
            $config = Config('wechat.payment.default');
        }

        $now_date = date('Y-m-d H:i:s');
        $data = array(
            'appid' => $config['app_id'], //公众账号ID
            'mch_id' => $config['mch_id'], //商户号
            'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
            'nonce_str' => \Illuminate\Support\Str::random(16), //随机字符串
            'out_refund_no' => $v->service_num, //商户退款单号
            'refund_fee' => intval(GetPriceTools::PriceCalc('*', $v->refund_price, 100)),
            'total_fee' => intval(GetPriceTools::PriceCalc('*', $v->all_price, 100)), //订单金额
            'transaction_id' => $v->transaction_id, //微信订单号
        );
        $data['sign'] = self::sign_data($data, $config['key']); //加密串
        $xml = self::ToXml($data); //数据包拼接
        dd($data);
        $res = self::postXmlCurl($config['refund_url'], $xml, 1);
        libxml_disable_entity_loader(true);
        if (!$res) {
            return true;
        }
        try {
            $xml = simplexml_load_string($res, 'SimpleXMLElement',
                LIBXML_NOCDATA);
            $xml = json_decode(json_encode($xml), true);
            dd($xml);

            $rrrModel = new RunRefundRecord();
            $rrrModel->order_type = 1;
            $rrrModel->order_id = $v->service_id;

            if ((strtolower($xml['return_msg']) === 'ok' || empty($xml['return_msg'])) &&
                strtolower($xml['return_code']) === 'success') {

                $mrr = MallRefundRecord::find($v->service_id);
                $mrr->status = 50;
                $mrr->refund_sub_at = $now_date;
                $mrr->run_refund = 2;
                $mrr->save();

                if (strtolower($xml['result_code']) == 'success') {
                    $rrrModel->is_success = 1;
                    $rrrModel->refund_money = GetPriceTools::PriceCalc('/', $xml['refund_fee'], 100);
                } else {
                    $rrrModel->is_success = 2;
                    $rrrModel->error_code = $xml['err_code'] ?? '';
                    $rrrModel->error_msg = $xml['return_msg'] . ' : ' . $xml['err_code_des'] ?? '';
                }
            } else {
                $rrrModel->is_success = 2;
                $rrrModel->error_code = $xml['err_code'] ?? '';
                $rrrModel->error_msg = $xml['return_msg'] . ' : ' . $xml['err_code_des'] ?? '';
            }
            $rrrModel->save();
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 微信-生成签名
     * @param $data
     * @param $appkey
     * @return string
     */
    public static function sign_data($data, $appkey)
    {
        ksort($data);
        $sign_temp = '';
        foreach ($data as $k => $v) {
            $sign_temp .= $k . '=' . $v . '&';
        }
        $sign_temp = trim($sign_temp, '&');
        $sign_temp = $sign_temp . '&key=' . $appkey;
        $sign = md5($sign_temp);
        return strtoupper($sign);
    }

    /**
     * 微信-数组转xml数据
     * @param $arr
     * @return string
     */
    public static function ToXml($arr)
    {
        if (!is_array($arr) || count($arr) <= 0) {
            echo '数据异常';
            die;
        }

        $xml = '';
        foreach ($arr as $k => $v) {
            if (is_numeric($v)) {
                $xml .= "<" . $k . ">" . $v . "</" . $k . ">";
            } else {
                $xml .= "<" . $k . "><![CDATA[" . $v . "]]></" . $k . ">";
            }
        }

        $xml = '<xml>' . $xml . '</xml>';

        return $xml;
    }

    public static function postXmlCurl($url, $vars, $pay_os, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //以下两种方式需选择一种
        //第一种方法，cert 与 key 分别属于两个.pem文件

        if ($pay_os == 1) {
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, base_path() . env('WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'));
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, base_path() . env('WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'));
        } elseif ($pay_os == 2) {
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, base_path() . env('WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'));
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, base_path() . env('WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'));
        } else {
            return false;
        }

        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错:$error\n<br>";
            curl_close($ch);
            return false;
        }
    }
}
