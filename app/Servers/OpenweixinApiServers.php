<?php
namespace App\Servers;


use Illuminate\Support\Facades\DB;
use WXBizMsgCrypt;

define("TOKEN", "HSby24Le9HLDzki40zAhlpcb8PuIEzOV");//定义识别码
define("EncodingAESKey", "G0Hbz7HqeV0VSXyhJgGHSfqZhHcAPebVPbdAeqNSY8v");//定义EncodingAESKey
define("AppID", "wxe24a425adb5102f6");//定义AppID

class OpenweixinApiServers {

    public function valid() {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg() {




        $timestamp  = $_GET['timestamp'] ??'';
        $nonce      = $_GET["nonce"] ??'';
        $msg_signature  = $_GET['msg_signature'] ??'';
        $encrypt_type = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? "aes" : "raw";



        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"] ??'';
        if (!empty($postStr)){
            if ($encrypt_type == 'aes'){
                $pc = new WXBizMsgCrypt(TOKEN,EncodingAESKey,AppID);

                //$pc = new WXBizMsgCrypt(TOKEN, EncodingAESKey, AppID);
//                $this->logger(" D \r\n".$postStr);
                $decryptMsg = "";  //解密后的明文
                $errCode = $pc->DecryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
                $postStr = $decryptMsg;
            }



            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch($RX_TYPE){
                case "event":
                    $this->receiveEvent($postObj);
                    break;
            }
        }
        echo "";  //向微信服务器返回空串
        exit;
    }

    private function receiveEvent($object){
        $content = "";

        switch ($object->Event){    //关注
            case "subscribe":
                $this->editData($object->FromUserName, 1);
                break;
            case "unsubscribe":     //取消关注
                $this->editData($object->FromUserName, 0);
                break;
        }

        $result = $this->transmitText($object,$content);

        return $result;

    }

    private function transmitText($object,$content){
        $textTpl = "%s0";

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);

        return $result;

    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


    function editData($fromUserName='', $state=0){

        if(!empty($fromUserName)){
            $res = DB::table('nlsg_wechat_openid')->where(['open_id'=>$fromUserName])->get();
            if(empty($res)){
                DB::table('nlsg_wechat_openid')
                    ->insert([
                        "open_id" => $fromUserName,
                        "status" => $state,
                        "created_at" => date("Y-m-d H:i:s"),
                        "updated_at" => date("Y-m-d H:i:s"),
                    ]);
            }else{
                DB::table('nlsg_wechat_openid')->where(['open_id'=>$fromUserName])
                    ->update([
                        "status" => $state,
                        "updated_at" => date("Y-m-d H:i:s"),
                    ]);
            }

        }
    }

}