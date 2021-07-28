<?php
namespace App\Servers;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

use App\Models\ImMedia;
use App\Models\ImMsgContentImg;
use Illuminate\Support\Facades\DB;

use OSS\OssClient;
use OSS\Core\OssException;

class AliUploadServers
{

    const AccessKeyId='LTAI5tL6ecVBmEVkQgjJgwrQ';
    const AccessKeySecret='vpuz7JdR4oklMLbLqsoeMUZlq2y6T5';
    const StorageLocation='outin-676a8a43e83811eb8be600163e108a8f.oss-cn-beijing.aliyuncs.com'; //存储地址

    const TemplateGroupId='296dad2655536aac8ef30199d528579b'; //视频转码ID
    const WorkflowId='04d6477bd874095952201ff69be42f4e'; //视频工作流ID

    const EndPoint='oss-cn-beijing.aliyuncs.com';//阿里oss上传

    const TypeArr=[
        '1'=>2870, //视频
        '2'=>2869, //音频
        '3'=>2872, //图片
        '4'=>2871, //文件
        '5'=>2899, //待删除
    ];
    public static $IMAGES_URL = 'https://audiovideo.ali.nlsgapp.com/';

    //初始化
    public function initVodClient($accessKeyId=self::AccessKeyId, $accessKeySecret=self::AccessKeySecret) {
        $regionId = 'cn-beijing';
        AlibabaCloud::accessKeyClient($accessKeyId, $accessKeySecret)
            ->regionId($regionId)
            ->asDefaultClient();
    }

    //参数请求提取
    public function AlibabaCloudRpcRequest($action,$query){
        try {
            $result= AlibabaCloud::rpc()
                ->product('vod')
                ->scheme('https') // https | http
                ->version('2017-03-21')
                ->action($action)
                ->method('POST')
                ->host('vod.cn-beijing.aliyuncs.com')
                ->options([
                    'query' => $query,
                ])
                ->request();
            return ['status'=>1,'data'=>$result->toArray()];
        } catch (ClientException $e) {
            return ['status'=>0,'msg'=>$e->getErrorMessage()];
        } catch (ServerException $e) {
            return ['status'=>0,'msg'=>$e->getErrorMessage()];
        }

    }

    /**
     * 获取视频上传地址和凭证
     */
    public function createUploadVideo($type,$fileName,$title) {

        $queryArr=[
            'FileName'=>$fileName, //视频源文件名 必须带扩展名   https://help.aliyun.com/document_detail/55396.htm?spm=a2c4g.11186623.2.11.65b95d4aPwYn08s
            'Title'=>$title,
            'CateId'=>self::TypeArr[$type], //分类ID  type 1 视频 2音频 3 图片 4文件
            'StorageLocation'=>self::StorageLocation, //存储地址
        ];
        //选择“不转码即分发”的方式上传视频文件后，点播播放服务仅支持MP4、FLV、MP3和M3U8格式的视频
        if($type==1) { //视频处理
//            $queryArr['TemplateGroupId']=self::TemplateGroupId; //转码模板组ID  只限视频
            $queryArr['WorkflowId']=self::WorkflowId; //工作流ID  只限视频 可截封面图
        }

        return self::AlibabaCloudRpcRequest('CreateUploadVideo',$queryArr);

    }

    //获取图片上传地址和凭证
    public function createUploadImage($type,$title,$ImageExt){
        $queryArr=[
            'ImageType'=>'default',
            'Title'=>$title,
            'ImageExt'=>$ImageExt, //扩展名 png jpg jpeg gif
            'CateId'=>self::TypeArr[$type], //分类ID  type 1 视频 2音频 3 图片 4文件
            'StorageLocation'=>self::StorageLocation, //存储地址
        ];

        return self::AlibabaCloudRpcRequest('CreateUploadImage',$queryArr);

    }

    //删除音视频时更改类型到待删除
    public function updateVideoInfo($VideoId,$CateId=5){

        $query=[
            'VideoId' => $VideoId,
            'CateId' => self::TypeArr[$CateId],
        ];

        return self::AlibabaCloudRpcRequest('UpdateVideoInfo',$query);

    }

    //删除点播图片
    public function DeleteImage($ImageIds){

        $query = [
            'ImageIds' => $ImageIds,
            'DeleteImageType' => "ImageId",
        ];

        return self::AlibabaCloudRpcRequest('DeleteImage',$query);

    }

    //获取播放地址接口
    public function getPlayInfo($VideoId,$ReturnData=false) {

        $query=[
            'VideoId' => $VideoId,
        ];

        $result=self::AlibabaCloudRpcRequest('GetPlayInfo',$query);
        if($result['status']==1){
            if($ReturnData===false) {
                return ['status' => 1, 'data' => ['url' => $result['data']['PlayInfoList']['PlayInfo'][0]['PlayURL']]];
            }else {
                return ['status' => 1, 'data' => ['url' => $result['data']]];
            }
        }else{
            return $result;
        }

    }

    //获取视频播放凭证
    public function getVideoPlayAuth($VideoId,$TimeOut) {

        $query=[
            'VideoId' => $VideoId,
            'AuthInfoTimeout' => $TimeOut,
        ];
        return self::AlibabaCloudRpcRequest('GetVideoPlayAuth',$query);

    }

    //定时抓取
    public function UploadMediaPull(){
        //获取图片
        $urlkey='https://cos.ap-shanghai.myqcloud.com/';
        $Imglist = ImMsgContentImg::query()->where(['media_id'=>0])->where('url', 'like', $urlkey . '%')
            ->select(['id', 'size', 'width','height','url'])
            ->limit(10)
            ->get();
        if($Imglist->isNotEmpty()){
            $ImgData=$Imglist->toArray();
            foreach ($ImgData as $key=>$val){
                self::UploadMediaByURL(3,$val['url'],$val);
            }
        }

    }

    //音视频拉取文件 https://help.aliyun.com/document_detail/100976.html?spm=a2c4g.11186623.6.1031.30f6d418f1Hpzw
    public function UploadMediaByURL($type='',$url='',$info=[]){

        if(in_array($type,[1,2,3])){
            require_once base_path('vendor').DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'Autoloader.php';
            date_default_timezone_set('PRC');
        }
        $now_date=date('Y-m-d H:i:s');
        if (in_array($type,[1,2])) { //拉取音视频

            //下载文件
            $DownRst=self::GetUrlDownload($url);
            if($DownRst['status']!=1){
                return $DownRst;
            }

            $uploader = new \AliyunVodUploader(self::AccessKeyId, self::AccessKeySecret,'cn-beijing');
            $uploadVideoRequest = new \UploadVideoRequest($DownRst['data']['filepath'], $DownRst['data']['filename'].'.'.$DownRst['data']['ext']);
            $uploadVideoRequest->setCateId(self::TypeArr[$type]);
            $uploadVideoRequest->setStorageLocation(self::StorageLocation);
            if($type==1) { //视频
                $uploadVideoRequest->setWorkflowId(self::WorkflowId);
            }
            $res = $uploader->uploadLocalVideo($uploadVideoRequest);
            $new_url=self::$IMAGES_URL.$res['UploadAddress'];
            $videoid=$res['VideoId']; //媒体id
        }else if($type==3){//拉取图片

            $filename=md5($url); //文件名
            $ImgType=getimagesize($url);
            $arr=explode('/',$ImgType['mime']);
            $ext=$arr[1]; //获取扩展名
            $filePath=storage_path('logs/'.$filename.'.'.$ext);
            if(!file_exists($filePath)) {
                try {
                    file_put_contents($filePath, file_get_contents($url)); //远程下载文件到本地
                } catch (\Exception $e) {
                    return ['status' => 0, 'data' => [], 'msg' => $url . '下载异常：' . $e->getMessage()];
                }
            }
            //上传图片
            $uploader = new \AliyunVodUploader(self::AccessKeyId, self::AccessKeySecret,'cn-beijing');
            $uploadImageRequest = new \UploadImageRequest($filePath, $filename.'.'.$ext);
            $uploadImageRequest->setCateId(self::TypeArr[3]);
            $res = $uploader->uploadLocalImage($uploadImageRequest);
            $new_url=$res['ImageURL'];
            $videoid=$res['ImageId']; //媒体id
            $file_name=$res['FileName']; //名称

        }
        if(in_array($type,[1,2,3])){
            DB::beginTransaction();
            $data = [
                'type' => $type,
                'url' => $new_url,
                'content_img_id' => $info['id'],
                'created_at' => $now_date
            ];
            $data['media_id'] = $videoid;
            if($type==3){
                $data['file_name']=$file_name;
                $data['size']=$info['size'];
                $data['width']=$info['width'];
                $data['height']=$info['height'];
            }
            $rst = DB::table(ImMedia::DB_TABLE)->insert($data);
            if ($rst === false) {
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败'];
            }
            //更新数据
            $upRst=ImMsgContentImg::query()->where(['id'=>$info['id']])->update(['media_id'=>$videoid,'ali_url'=>$new_url]);
            if ($upRst === false) {
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败'];
            }
            DB::commit();
            unlink($filePath);
            return ['status' => 1, 'data' => [], 'msg' => '抓取成功'];


        }
        if($type==4) {//拉取文件上传oss

            //下载文件
            $RstData=self::GetUrlDownload($url);
            if($RstData['status']==0){
                return $RstData;
            }
            $filename=$RstData['data']['filename'];
            $ext=$RstData['data']['ext'];
            $filePath=$RstData['data']['filepath'];
            //上传文件
            $PushRst=self::PushOSS($filename,$ext,$filePath);
            if($PushRst['status']==0){
                return ['status' => 0, 'data' => [], 'msg' => '拉取失败'];
            }
            DB::beginTransaction();
            $now_date=date('Y-m-d H:i:s');
            $data = [
                'type' => 4,
                'url' => self::$IMAGES_URL.$PushRst['data']['name'],
                'file_name' => $PushRst['data']['name'],
                'created_at' => $now_date
            ];
            $rstId = DB::table(ImMedia::DB_TABLE)->insertGetId($data);
            if ($rstId === false) {
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败'];
            }
            //初始化媒体id
            $UpRst=DB::table(ImMedia::DB_TABLE)->where('id', $rstId)->update(['media_id' => $rstId,'updated_at' => $now_date]);
            if($UpRst===false){
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败'];
            }
            DB::commit();
            unlink($filePath);
            return ['status' => 1, 'data' => [], 'msg' => '抓取成功'];
        }

    }

    //从第三方源地址下载文件
    public function GetUrlDownload($url){

        $filename = md5($url); //文件名
        $arr = explode('.', $url);
        $ext = $arr[count($arr) - 1]; //扩展名

        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
        $filePath = storage_path('logs/' . $filename . '.' . $ext);
        if (!file_exists($filePath)) {
            try {
                file_put_contents($filePath, file_get_contents($url)); //远程下载文件到本地
            } catch (\Exception $e) {
                return ['status' => 0, 'data' => [], 'msg' => $url . '下载异常：' . $e->getMessage()];
            }
        }
        $data=[
            'filename'=>$filename,
            'ext'=>$ext,
            'filepath'=>$filePath
        ];

        return ['status' => 1, 'data' => $data, 'msg' => '下载成功'];
    }

    //OSS文件上传
    public function PushOSS($filename,$ext,$filePath){

        // Endpoint以杭州为例
        $endpoint = self::EndPoint;
        // 存储空间名称
        $bucket = Config('web.Ali.BUCKET_ALI');

        try{

            //上传阿里
            $ossClient = new OssClient(self::AccessKeyId, self::AccessKeySecret, $endpoint);
            // 设置文件名称
            $object = '1111group/' . date('Ymd') . $filename . '.' . $ext;
            // 文件内容
            $doesres = $ossClient->doesObjectExist($bucket, $object); //获取是否存在
            if ($doesres) {
                return [ 'status' => 0,'data'=>[],'msg'=>'文件名已存在'];
            }
            $ossClient->uploadFile($bucket, $object, $filePath);

            $data=[
                'url' => Config('web.Ali.IMAGES_URL'),
                'name' => $object,
            ];

            return ['status' => 1, 'data' =>$data , 'msg' => '上传成功'];

        } catch(OssException $e) {
            return [ 'status' => 0,'data'=>[],'msg'=>$e->getMessage()];
        }

    }

    //oss文件删除
    public function DelOss($name){
        try {
            $ossClient = new OssClient(self::AccessKeyId, self::AccessKeySecret, self::EndPoint);

            // 存储空间名称
            $bucket= Config('web.Ali.BUCKET_ALI');
            // 文件名称
            $object=$name;

            $doesres = $ossClient->doesObjectExist($bucket, $object); //获取是否存在
            if($doesres){
                // 文件内容
                $ossClient->deleteObject($bucket, $object);
                return ['status' => 1,  'msg' => '上传成功'];
            }else{
                return ['status' => 0,  'msg' => '文件不存在'];
            }

        } catch (OssException $e) {
            return ['status' => 0,  'msg' =>  $e->getMessage()];
        }
    }


}
