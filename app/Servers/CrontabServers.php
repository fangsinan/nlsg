<?php


namespace App\Servers;


use App\Models\GetPriceTools;
use App\Models\MallRefundRecord;
use App\Models\RunRefundRecord;


class CrontabServers
{
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
                    $this->aliRefund($v);
                    break;
            }
        }
    }

    //todo ali退款
    public function aliRefund($v)
    {
        require_once base_path() . '/vendor/alipay-sdk/aop/AopClient.php';
        require_once base_path() . '/vendor/alipay-sdk/aop/request/AlipayTradeRefundRequest.php';
        $aop = new \AopClient();

        $aop->appId = env('ALI_APP_ID');
        $aop->alipayrsaPublicKey = env('ALI_PUBLIC_KEY');
        $aop->rsaPrivateKey = env('ALI_PRIVATE_KEY');
        $aop->gatewayUrl = env('ALI_PAYMENT_REFUND_URL');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';

        //退款订单信息
        $out_trade_no = $v->ordernum; //out_trade_no 商户订单号1
        $trade_no = $v->transaction_id; //trade_no 支付宝交易号1
        $refund_amount = $v->refund_price; //refund_amount 退款金额 不能大于订单金额，单位为元1
        $refund_reason = '正常退款'; //refund_reason 退款原因1
        $out_request_no = $v->service_num; //out_request_no
        $operator_id = 0; //operator_id 商户的操作员编号1
        $request = new \AlipayTradeRefundRequest();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"$out_trade_no\"," .
            "\"trade_no\":\"$trade_no\"," .
            "\"refund_amount\":$refund_amount," .
            "\"refund_reason\":\"$refund_reason\"," .
            "\"out_request_no\":\"$out_request_no\"," .
            "\"operator_id\":\"$operator_id\"" .
            "}");

        $now_date = date('Y-m-d H:i:s');
        try {
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_",
                    $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            $rrrModel = new RunRefundRecord();
            $rrrModel->order_type = 1;
            $rrrModel->order_id = $v->service_id;
            if (!empty($resultCode) && $resultCode == 10000) {
                $mrr = MallRefundRecord::find($v->service_id);
                $mrr->status = 50;
                $mrr->refund_sub_at = $now_date;
                $mrr->save();
                $rrrModel->is_success = 1;
                $rrrModel->refund_money = $result->$responseNode->coderefund_fee;
            } else {
                $rrrModel->is_success = 2;
                $rrrModel->error_code = $result->$responseNode->sub_code ?? '';
                $rrrModel->error_msg = $result->$responseNode->msg . ' : ' .
                    $result->$responseNode->sub_msg ?? '';
            }
            $rrrModel->save();
        } catch (\Exception $e) {
            return true;
        }
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
            'refund_fee' => $v->refund_price * 100, //退款金额
            'total_fee' => $v->all_price * 100, //订单金额
            'transaction_id' => $v->transaction_id, //微信订单号
        );
        $data['sign'] = self::sign_data($data, $config['key']); //加密串
        $xml = self::ToXml($data); //数据包拼接
        $res = self::postXmlCurl($config['refund_url'], $xml, 1);
        libxml_disable_entity_loader(true);

        try {
            $xml = simplexml_load_string($res, 'SimpleXMLElement',
                LIBXML_NOCDATA);
            $xml = json_decode(json_encode($xml), true);

            $rrrModel = new RunRefundRecord();
            $rrrModel->order_type = 1;
            $rrrModel->order_id = $v->service_id;

            if ((strtolower($xml['return_msg']) === 'ok' || empty($xml['return_msg'])) &&
                strtolower($xml['return_code']) === 'success') {

                $mrr = MallRefundRecord::find($v->service_id);
                $mrr->status = 50;
                $mrr->refund_sub_at = $now_date;
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
        } catch (\Exception $e) {
            return true;
        }
        return true;
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
        $result = strtoupper($sign);
        return $result;
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
