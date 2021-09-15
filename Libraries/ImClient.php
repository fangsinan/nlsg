<?php
namespace Libraries;

class ImClient
{

    public static function index(){
        return ;
    }

    //拼接im 所需参数
    public static function get_im_url($url){

        if(empty($url)){
            return '';
        }

        $url.='?'.http_build_query([
                'sdkappid' => config('env.OPENIM_APPID'),
                'identifier' => config('web.Im_config.admin'),
                'usersig' => ImClient::getUserSig(config('web.Im_config.admin')),
                'random' => rand(0,4294967295),
                'contenttype'=>'json',
            ]);
        return $url;
    }


    public static function getUserSig($userId){
        if(empty($userId)){
            return false;
        }
        $key = config('env.OPENIM_SECRETKEY');
        $sdkappid = config('env.OPENIM_APPID');

        $tlsClass = new \TLSSigAPIv2($sdkappid, $key);
        return $tlsClass->genUserSig($userId);

    }



    public static function curlPost($url, $data = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //显示获取的头部信息必须为true否则无法看到cookie
        //curl_setopt($curl, CURLOPT_HEADER, true);
//        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);// 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// 使用自动跳转
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);// 发送一个常规的Post请求
            if (is_array($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));// Post提交的数据包
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// Post提交的数据包 可以是json数据
            }
        }
        curl_setopt($curl, CURLOPT_COOKIESESSION, true); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        //curl_setopt($curl, CURLOPT_TIMEOUT, 30);// 设置超时限制防止死循环
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        $tmpInfo = curl_exec($curl);
        curl_close($curl);
        if (empty($tmpInfo)) {
            return false;
        }
        return $tmpInfo;
    }



    public static function curlGet($url=""){

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }




}