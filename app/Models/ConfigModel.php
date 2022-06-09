<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use OSS\OssClient;
use AlibabaCloud\Client\AlibabaCloud;
use ClientException;
use ServerException;

/**
 * Description of Config
 *
 * @author wangxh
 */
class ConfigModel extends Base
{

    protected $table = 'nlsg_config';

    protected static $MIME_TYPE_TO_TYPE = [
        'image/jpeg' => 'jpg', 'image/png' => 'png'
    ];

    //1:邮费  2:特价优先级
    public static function getData($id, $flag = 0)
    {
        //$expire_num = 3600;
        $expire_num = 60;
        $cache_key_name = 'v4_config_' . $id;

        $res = Cache::get($cache_key_name);
        if ($flag === 1 || empty($res)) {
            $res = self::getFromDb($id);
            Cache::put($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

    protected static function getFromDb($id)
    {
        $res = ConfigModel::find($id);
        if (empty($res)) {
            return '';
        }
        $res = $res->toArray();
        return $res['value'];
    }

    public function tempConfig($id, $user_id)
    {
        switch (intval($id)) {
            case 1:
                if (empty($user_id)) {
                    return ['code' => true, 'data' => ''];
                }
                $list = ConfigModel::getData(48);
                $list = explode(';', $list);
                $list = array_filter($list);
                $count = count($list);
                $key = $user_id % $count;
                $res = explode(',', $list[$key]);
                return ['code' => true, 'url' => $res[0], 'str' => $res[1]];
            default:
                return ['code' => true, 'data' => ''];
        }
    }

    //上传操作
    public static function base64Upload($type_flag, $file_base64)
    {


        $dir = 'nlsg/';
        switch ($type_flag) {
            case 1:
                $dir .= 'headimg';
                break;
            case 2:
                $dir .= 'works';
                break;
            case 3:
                $dir .= 'authorpt';
                break;
            case 4:
                $dir .= 'goods';
                break;
            case 5:
                $dir .= 'idcard';
                break;
            case 6:
                $dir .= 'banner';
                break;
            case 7:
                $dir .= 'booklist';
                break;
            case 8:
                $dir .= 'company';
                break;
            case 9:
                $dir .= 'feedback';
                break;
            case 10:
                $dir .= 'evaluate';
                break;
            case 100:
                $dir .= 'other';
                break;
            case 101:
                $dir .= 'temp';
                break;
            case 102:
                $dir .= 'meeting';
                break;
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file_base64, $match)) {
            $accessKeyId = Config('web.Ali.ACCESS_KEY_ALI');
            $accessKeySecret = Config('web.Ali.SECRET_KEY_ALI');
            $endpoint = "oss-cn-beijing.aliyuncs.com";
            //上传阿里
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $dir = $dir . '/' . date('YmdHis');

            // 存储空间名称
            $bucket = Config('web.Ali.BUCKET_ALI');
            $ext = self::$MIME_TYPE_TO_TYPE["image/" . $match[2]] ?? 'jpg'; //扩展名
            $content = base64_decode(str_replace($match[1], '', $file_base64));
            // 文件名称
            $object = $dir . rand(100000, 999999) . '.' . $ext;
            // 文件内容
            $doesres = $ossClient->doesObjectExist($bucket, $object); //获取是否存在
            if ($doesres) {
                return ['code' => 1, 'msg' => '文件已存在'];
            } else {
                $object = $dir . rand(100000, 999999) . '.' . $ext;
            }
            $ossClient->putObject($bucket, $object, $content);
            return [
                'code' => 0,
                'url' => Config('web.Ali.IMAGES_URL'),
                'name' => $object
            ];

        } else {
            return ['code' => 1, 'msg' => 'base64码解析错误'];
        }
    }

    public static function AliProof(){
        $regionId = "cn-beijing";
        // 设置调用者（RAM用户或RAM角色）的AccessKey ID和AccessKey Secret。
        AlibabaCloud::accessKeyClient(Config('web.STSAli.ACCESS_KEY_ALI'), Config('web.STSAli.SECRET_KEY_ALI'))
                    ->regionId($regionId)->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                                  ->product('Sts')
                                  ->scheme('https') // https | http
                                  ->version('2015-04-01')
                                  ->action('AssumeRole')
                                  ->method('POST')
                                  ->host('sts.aliyuncs.com')
                                  ->options([
                                                'query' => [
                                                  'RegionId' => $regionId,
                                                  'RoleArn' => "acs:ram::1255787033270118:role/ramosstest",
                                                  'RoleSessionName' => "RamOssTest",
                                                ],
                                            ])
                                  ->request();
            $ali = $result->toArray();
            return $ali['Credentials'];
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
        return [];
    }


}
