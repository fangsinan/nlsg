<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use App\Models\PayRecord;
use Illuminate\Http\Request;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;
use EasyWeChat\OpenPlatform\Server\Guard;
use App\Servers\OpenweixinApiServers;

class CallbackController extends Controller
{
    //APP端   接收微信发送的异步支付结果通知
    public function WechatNotify(Request $request){
        $config = Config('wechat.payment.default');
//        $config = Config('wechat.payment.old_default');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            PayRecord::PayLog('Wechat notify',json_encode($message));
//            \Log::info('Wechat notify'.json_encode($message));
            // 你的逻辑
            $data = [
                'out_trade_no'      => $message['out_trade_no'], //获取订单号
                'total_fee'         => $message['total_fee']/100, //价格
                'transaction_id'    => $message['transaction_id'], //交易单号
                'attach'            => $message['attach'],
                'pay_type'          => 2,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
            ];
            $res = WechatPay::PayStatusUp($data);

            if($res == true){
                //在闭包函数里 return true; 才代表处理完成。
                return true;
            }
            // $fail 为一个函数，触发该函数可向微信服务器返回对应的错误信息，微信会稍后重试再通知。

            $fail('Order not exists.');
        });

        return $response;

    }


    //H5端   接收微信发送的异步支付结果通知
    public function WechatNotifyJsapi(Request $request){


        $config = Config('wechat.payment.default');
//        $config = Config('wechat.payment.old_default');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            PayRecord::PayLog('Wechat notify',json_encode($message));
//            \Log::info('Wechat h5 notify'.json_encode($message));
            // 你的逻辑
            $data = [
                'out_trade_no'      => $message['out_trade_no'], //获取订单号
                'total_fee'         => $message['total_fee']/100, //价格
                'transaction_id'    => $message['transaction_id'], //交易单号
                'attach'            => $message['attach'],
                'pay_type'          => 1,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
            ];
            $res = WechatPay::PayStatusUp($data);

            if($res == true){
                //在闭包函数里 return true; 才代表处理完成。
                return true;
            }
            // $fail 为一个函数，触发该函数可向微信服务器返回对应的错误信息，微信会稍后重试再通知。

            $fail('Order not exists.');
        });

        return $response;

    }


    //APP端   支付宝回调
    public function AliNotify(Request $request)
    {
        $config = Config('pay.alipay');
        $alipay = Pay::alipay($config);

        try{
            $res_data = $alipay->verify(); // 是的，验签就这么简单！
            PayRecord::PayLog('Alipay notify',json_encode($res_data->all()));
            //元数据
//            Log::info('Alipay notify', $res_data->all());

            $res_data = $res_data->all();
            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            $data = [
                'out_trade_no' => $res_data['out_trade_no'], //获取订单号
                'total_fee' => $res_data['total_amount'],    //价格
                'transaction_id' => $res_data['trade_no'],   //交易单号
                'attach' => $res_data['passback_params'],
                'pay_type' => 3,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
            ];
            WechatPay::PayStatusUp($data);

            return $alipay->success();
        } catch (\Exception $e) {
            Log::debug('Alipay notify', [ $e->getMessage() ]);
            $e->getMessage();
        }

        //return $alipay->success();// laravel 框架中请直接 `return $alipay->success()`
    }


    //Im 回调  POST请求
    public function callbackMsg(Request $request){

        $params = $request->input();
        PayRecord::PayLog('im_log',json_encode($params));
//        \Log::info('im_log'.json_encode($params));
//        $json = '{"CallbackCommand":"Sns.CallbackFriendDelete","PairList":[{"From_Account":"211172","To_Account":"425214"},{"From_Account":"425214","To_Account":"211172"}],"ClientIP":"36.112.173.178","OptPlatform":"iOS","RequestId":"2ea4e023-2859-4512-b629-3089f77dff70","SdkAppid":"1400483163","contenttype":"json"}';
//        $params = json_decode($json,true);

        if( empty($params['SdkAppid']) ){
            return response()->json(["ActionStatus"=>"FAIL", "ErrorInfo"=>'SdkAppid error',  "ErrorCode"=> 1  ]);
        }

        if( $params['SdkAppid'] != config('env.OPENIM_APPID')){
            return response()->json(["ActionStatus"=>"FAIL", "ErrorInfo"=>'SdkAppid error',  "ErrorCode"=> 1  ]);
        }

        switch ($params['CallbackCommand']){
            //case 'C2C.CallbackBeforeSendMsg': //消息之前x
            case 'C2C.CallbackAfterSendMsg': //消息之后回调
            case 'Group.CallbackAfterSendMsg': //群聊消息
                $result = ImMsgController::sendMsg($params);
                break;

            case 'Group.CallbackAfterCreateGroup': //创建群聊天
                $result = ImGroupController::addGroup($params);
                break;
            case 'Group.CallbackAfterGroupDestroyed': //解散群聊天
            case 'Group.CallbackAfterGroupInfoChanged': //群组资料修改之后回调
                $result = ImGroupController::editGroup($params);
                break;

            case 'Group.CallbackAfterNewMemberJoin': //新成员入群之后回调
                $result = ImGroupController::joinGroup($params);
                break;
            case 'Group.CallbackAfterMemberExit': //群成员离开之后回调
                $result = ImGroupController::exitGroup($params);
                break;

            case 'Sns.CallbackFriendAdd':  //添加好友
                $result = ImFriendController::friendAdd($params);
                break;

            case 'Sns.CallbackFriendDelete':  //删除好友
                $result = ImFriendController::friendDel($params);
                break;

            case 'Sns.CallbackBlackListAdd'://添加黑名单
                $result = ImFriendController::blackListAdd($params);
                break;
            case 'Sns.CallbackBlackListDelete'://删除黑名单
                $result = ImFriendController::blackListDel($params);
                break;
            default :
                $result = true;

        }

        if($result){
            return response()->json([
                "ActionStatus"=>"OK",
                "ErrorInfo"=>"",
                "ErrorCode"=> 0 // 0为回调成功，1为回调出错
            ]);
        }else{
            return response()->json([
                "ActionStatus"=>"FAIL",
                "ErrorInfo"=>'',
                "ErrorCode"=> 1 // 0为回调成功，1为回调出错
            ]);
        }


    }

    /**--------------以下文件备份----------------*/
    //微信公众号回调
    public function callBackWeixinEvent(Request $request){
        PayRecord::PayLog('openweixin_msg',json_encode($request->input()));

        $wechatObj = new OpenweixinApiServers();//实例化wechatCallbackapiTest类

        $openPlatform = Factory::openPlatform($wechatObj->getConfig());
        $server = $openPlatform->server;

        // 处理授权成功事件，其他事件同理
        $server->push(function ($message) {


            PayRecord::PayLog('openweixin_msg-info',json_encode($message));

            // $message 为微信推送的通知内容，不同事件不同内容，详看微信官方文档
            // 获取授权公众号 AppId： $message['AuthorizerAppid']
            // 获取 AuthCode：$message['AuthorizationCode']
            // 然后进行业务处理，如存数据库等...
        }, Guard::EVENT_AUTHORIZED);

        return $server->serve();




//        //解析XML
//        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
//        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if(!isset($_GET["echostr"])){
            $wechatObj->responseMsg();

        }else{
            $wechatObj->valid();
        }
    }





    public  function getWechatVerify(Request $request) {
        PayRecord::PayLog('openweixin_Verify',json_encode($request->input()));

        $wechatObj = new OpenweixinApiServers();//实例化wechatCallbackapiTest类

        $openPlatform = Factory::openPlatform($wechatObj->getConfig());
        $server = $openPlatform->server;

        // 处理授权成功事件，其他事件同理
        $server->push(function ($message) {

            PayRecord::PayLog('openweixin_Verify-info',json_encode($message));

            // $message 为微信推送的通知内容，不同事件不同内容，详看微信官方文档
            // 获取授权公众号 AppId： $message['AuthorizerAppid']
            // 获取 AuthCode：$message['AuthorizationCode']
            // 然后进行业务处理，如存数据库等...
        }, Guard::EVENT_AUTHORIZED);


        return $server->serve();

    }

    /**--------------以下文件备份----------------*/

}
