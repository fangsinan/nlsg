<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

use OSS\OssClient;
use OSS\Core\OssException;

use Illuminate\Support\Facades\Log;

//https://help.aliyun.com/document_detail/123461.html?spm=a2c4g.11186623.6.1074.525958a4xbtMvZ      安装php -d memory_limit=-1 composer.phar require alibabacloud/sdk
//https://next.api.aliyun.com/api/vod/2017-03-21/GetPlayInfo?params={%22VideoId%22:%2213a3ba6d4f1b4c7ba1b585cad344562e%22}&sdkStyle=old    接口调试
class AliUploadController extends Controller
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
    public function initVodClient($accessKeyId, $accessKeySecret) {
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
     * @api {post} /api/v4/upload/push_ali_auth   上传音视频点播和图片
     * @apiName PushAliAuth
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {int} type  类型  1 视频 2音频 3图片
     * @apiParam {string} title  标题
     * @apiParam {string} filename   文件名(带扩展名)   音视频传参
     * @apiParam {string} imageext   文件扩展名   图片传参
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *   "code": 200,
     *   "msg": "成功",
     *   "now": 1627033302,
     *   "data": { //音视频返回
     *      "VideoId": "42bbabf7312346428ca2b2773d5e6fe9",
     *      "UploadAddress": "eyJFbmRwb2ludCI6Imh0dHBzOi8vb3NzLWNuLWJlaWppbmcuYWxpeXVuY3MuY29tIiwiQnVja2V0Ijoib3V0aW4tNjc2YThhNDNlODM4MTFlYjhiZTYwMDE2M2UxMDhhOGYiLCJGaWxlTmFtZSI6Im9yaWdpbmFsL3dvcmtmbG93L2E3OGE3YWQtMTdhZDJiZGY1OTgtMDAwNC03NjIxLWI0MC04MTc2Ni5tcDQifQ==",
     *      "RequestId": "562A2132-4999-4905-AD8F-6160F4C9BF71",
     *      "UploadAuth": "eyJTZWN1cml0eVRva2VuIjoiQ0FJUzFnUjFxNkZ0NUIyeWZTaklyNWZ6QWVtTW9KVnRnYmlqY3hMVGxWZ0ZTdlp0dUlQTnB6ejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2NsclVxRWNJWUdoeWJOWkFydE1zUHFGcnhKcGZadjh1ODRZQURpNUNqUWJvenNPRSttNTI4V2Y3d2FmK0FVQS9HQ1RtZDVNMFlvOWJUY1RHbFFDWnVXLy90b0pWN2I5TVJjeENsWkQ1ZGZybC9MUmRqcjhsbzF4R3pVUEcyS1V6U24zYjNCa2hsc1JZZTcyUms4dmFIeGRhQXpSRGNnVmJtcUpjU3ZKK2pDNEM4WXM5Z0c1MTlYdHlwdm9weGJiR1Q4Q05aNXo5QTlxcDlrTTQ5L2l6YzdQNlFIMzViNFJpTkw4L1o3dFFOWHdoaWZmb2JIYTlZcmZIZ21OaGx2dkRTajQzdDF5dFZPZVpjWDBha1E1dTdrdTdaSFArb0x0OGphWXZqUDNQRTNyTHBNWUx1NFQ0OFpYVVNPRHREWWNaRFVIaHJFazRSVWpYZEk2T2Y4VXJXU1FDN1dzcjIxN290ZzdGeXlrM3M4TWFIQWtXTFg3U0IyRHdFQjRjNGFFb2tWVzRSeG5lelc2VUJhUkJwYmxkN0JxNmNWNWxPZEJSWm9LK0t6UXJKVFg5RXoycExtdUQ2ZS9MT3M3b0RWSjM3V1p0S3l1aDRZNDlkNFU4clZFalBRcWl5a1QwbkZncGZUSzFSemJQbU5MS205YmFCMjUvelcrUGREZTBkc1Znb0psS0VwaUdXRzNSTE5uK3p0Sjl4YmtlRStzS1Vrdk9TK3NOcFRGQWp0b3NQVkZpSWU0Wm5vZ0krdS9Mc3RCbktxTC9xQW43dCtYQTU5ZGplOW8wSXEya2NKNjM3M3JMTTVHQ0E1U2JNT3ZGaHh2MjZBak0vSFU2RkhGVmkyKzJYaTM0OW9CUU1ybnE1SVI1MzZTN0tpRC9nSkpWRGpLRFJuQzBkWC9vSXhMckNOeDZrLzNSOUQreU83NDBDVVBoWllQdDBWZkt4elpmUkVROXVSTmZhR29BQlowczMrT1VBTmVkeU1PMmNrUlZENWdkcGNHSU1QTmhxVDdQOENjWmFubno4bENsM0tsTGtLdVBZUSttWW5QS053OHN6TTN5ODVKMlNRL3BsdFBWMEpuZXo4WTIvUlBNTFlHNCs4SkFyUHNtQ051YTh6TzFSVFNud1lCdVIwa09OOEN6OFB0ZXhXL3VuNXFJYWJQY1pXVGxaWTc3QXgvL0FCbGlXQ01qNHRUWT0iLCJBY2Nlc3NLZXlJZCI6IlNUUy5OVEZKUzhNSkw2ekhxNGJxWFBGeUFXRmZFIiwiRXhwaXJlVVRDVGltZSI6IjIwMjEtMDctMjNUMTA6NDE6NDJaIiwiQWNjZXNzS2V5U2VjcmV0IjoiRUp0dXc3UEFXYk5VdXk3WTEzUmhucWRhempTTGVKV1hxc0NSb1lySFNRUE4iLCJFeHBpcmF0aW9uIjoiMzYwMCIsIlJlZ2lvbiI6ImNuLWJlaWppbmcifQ=="
     *   }
     *   "data":{ //图片返回
     *      "FileURL": "https://outin-676a8a43e83811eb8be600163e108a8f.oss-cn-beijing.aliyuncs.com/image/default/7D1F2DC6CF9946FEAC4F8EC76F731F16-6-2.jpg",
     *      "UploadAddress": "eyJFbmRwb2ludCI6Imh0dHBzOi8vb3NzLWNuLWJlaWppbmcuYWxpeXVuY3MuY29tIiwiQnVja2V0Ijoib3V0aW4tNjc2YThhNDNlODM4MTFlYjhiZTYwMDE2M2UxMDhhOGYiLCJGaWxlTmFtZSI6ImltYWdlL2RlZmF1bHQvN0QxRjJEQzZDRjk5NDZGRUFDNEY4RUM3NkY3MzFGMTYtNi0yLmpwZyJ9",
     *      "RequestId": "EE425893-8C74-4C33-9CF4-6BF771033EEA",
     *      "UploadAuth": "eyJTZWN1cml0eVRva2VuIjoiQ0FJUzB3UjFxNkZ0NUIyeWZTaklyNVdHUCt6a21aVnp6dk8vU1JQa3NuVURXUGhEbDRQK3BEejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2NsclVxRWNJWUdoeWJOWkFydE1zUHFGcnhKcGZadjh1ODRZQURpNUNqUWNBMjRlNCttNTI4V2Y3d2FmK0FVQkxHQ1RtZDVNQVlvOWJUY1RHbFFDWnVXLy90b0pWN2I5TVJjeENsWkQ1ZGZybC9MUmRqcjhsbzF4R3pVUEcyS1V6U24zYjNCa2hsc1JZZTcyUms4dmFIeGRhQXpSRGNnVmJtcUpjU3ZKK2pDNEM4WXM5Z0c1MTlYdHlwdm9weGJiR1Q4Q05aNXo5QTlxcDlrTTQ5L2l6YzdQNlFIMzViNFJpTkw4L1o3dFFOWHdoaWZmb2JIYTlZcmZIZ21OaGx2dkRTajQzdDF5dFZPZVpjWDBha1E1dTdrdTdaSFArb0x0OGphWXZqUDNQRTNyTHBNWUx1NFQ0OFpYVVNPRHREWWNaRFVIaHJFazRSVWpYZEk2T2Y4VXJXU1FDN1dzcjIxN290ZzdGeXlrM3M4TWFIQWtXTFg3U0IyRHdFQjRjNGFFb2tWVzRSeG5lelc2VUJhUkJwYmxkN0JxNmNWNWxPZEJSWm9LK0t6UXJKVFg5RXoycExtdUQ2ZS9MT3M3b0RWSjM3V1p0S3l1aDRZNDlkNFU4clZFalBRcWl5a1Qwa0ZncGZUSzFSemJQbU5MS205YmFCMjUvelcrUGREZTBkc1Znb0pWS0RwaUdXRzNSTE5uK3p0Sjl4YmtlRStzS1Vrdk9TK3NOcFRGQWp0b3NQVkZpSWU0Wm5vZ0krdS9Mc3RCbktxTC9xQW43dC8yOHg5ZFNmdmFzM3NCRTdJNnI2MmJITTUyU0M1eVRJUDVOVXdwbUhCRGRkSmoyc1lHRjh6ZnlvZ1hZS21nc01pV25jT1d4RXRnM0JqVDdvSXBGQmlLTFNteTRmWC9sSjVjM2NTaWE5K0Z0bkJlbUE2cTB3UmZoWWUrUkRRbUFFQ3ZQZ0xUZU5Hb0FCbnJ4ZzVsaUxjNjdwQm5xTlFVWjZPUzBlMmNsQUNNdWw5N1IrcHp1aHpIeTdlTjBYWlNxMTlrNkJxVWxrL1pGYVNrV2RNUDlKUVp5dFJMc2hPR1BGemZ3OWFJT3BIeGlyWXZyZkFLaVp1ckdxeHRaRUY4dkZzazBUdFpRV1M1dy90bWJIYjVMLytwMFVxWHNhTmU2VklOSWVwUGlBWVk0WTluditwUGxBcjhNPSIsIkFjY2Vzc0tleUlkIjoiU1RTLk5WM3RWUHRKUnkxVEs1VVZ1VlR3b3hGVUYiLCJFeHBpcmVVVENUaW1lIjoiMjAyMS0wNy0yM1QxMDo1NDozM1oiLCJBY2Nlc3NLZXlTZWNyZXQiOiJINEZpeUFTYTdEQjllNng4cVBnNlIzZXVZRFpqQXBqRjJFRHoyY0dOWFJQVCIsIkV4cGlyYXRpb24iOiIzNjAwIiwiUmVnaW9uIjoiY24tc2hhbmdoYWkifQ==",
     *      "ImageId": "d850661b8add4c3f8d6b5deb04f1f8a8",
     *      "ImageURL": "https://audiovideo.ali.nlsgapp.com/image/default/7D1F2DC6CF9946FEAC4F8EC76F731F16-6-2.jpg" //图片地址使用这个
     *   }
     *}
     */
    //如果视频上传凭证失效（有效期为3000秒） 50分钟
    public function PushAliAuth(Request $request)
    {

        $params = $request->input();
        $type = (empty($params['type']))?0:$params['type'];
        $title = (empty($params['title']))?'':$params['title'];
        $filename = (empty($params['filename']))?'':$params['filename'];
        $imageext = (empty($params['imageext']))?'':$params['imageext'];
        //type 1 视频 2音频 3 图片 4文件
        if (!in_array($type, [1, 2,3])) {
            return $this->error(0, '上传类型有误');
        }
        if(empty($title)){
            return $this->error(0, '标题不能为空');
        }
        if(in_array($type,[1,2])) {
            if (empty($filename)) {
                return $this->error(0, '文件名不能为空');
            }
        }else{
            if (empty($imageext)) {
                return $this->error(0, '扩展名不能为空');
            }
        }
        try {

            self::initVodClient(self::AccessKeyId, self::AccessKeySecret);

            if(in_array($type,[1,2])) {
                //获取视频上传地址和凭证
                $result = $this->createUploadVideo($type, $filename, $title);
            }else {
                //图片上传凭证
                $result = $this->createUploadImage($type, $title, $imageext);
            }

            if($result['status']==1){
                return $this->success($result['data']);
            }else{
                return $this->error(0, $result['msg']);
            }

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage());
        }

    }
    /**
     * @api {post} /api/v4/upload/del_ali_ydb   删除音视频点播
     * @apiName DelAliYdb
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {int} type   类型  1 视频 2音频 3图片
     * @apiParam {string} videoid   点播id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *   "code": 200,
     *   "msg": "成功",
     *   "now": 1627033302,
     *   "data": { //音视频返回
     *      "RequestId": "BC452DE2-5BAB-45A7-989B-C3F62CC41855"
     *   }
     *}
     */
    public function DelAliYdb(Request $request)
    {

        $params = $request->input();
        $type = (empty($params['type']))?0:$params['type'];
        $videoid = (empty($params['videoid']))?'':$params['videoid'];

        //type 1 视频 2音频 3 图片 4文件
        if (!in_array($type, [1, 2, 3])) {
            return $this->error(0, '类型有误');
        }
        if(empty($videoid)){
            return $this->error(0, '媒体id不能为空');
        }

        try {

            self::initVodClient(self::AccessKeyId, self::AccessKeySecret);
            if(in_array($type,[1,2])) {
                //删除时修改类型
                $result = $this->updateVideoInfo($videoid);
            }else{
                //删除图片
                $result=$this->DeleteImage($videoid);
            }

            if($result['status']==1){
                return $this->success($result['data']);
            }else{
                return $this->error(0, $result['msg']);
            }

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage());
        }

    }

    /**
     * @api {post} /api/v4/upload/get_play   获取播放权限
     * @apiName GetPlay
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {int} flag   标记  1 播放地址 2播放凭证
     * @apiParam {string} videoid   点播id
     * @apiParam {string} timeout   有效时长(默认10分)    flag为2时传入
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *   "code": 200,
     *   "msg": "成功",
     *   "now": 1627033302,
     *   "data": {
     *       "url": "https://audiovideo.ali.nlsgapp.com/13a3ba6d4f1b4c7ba1b585cad344562e/9e59679532694464973a0d0abef64977-6e86e0f1fab7f47b962a9711b2a9eb8d-ld.mp4" //flag为1返回
     *       "PlayAuth": "eyJTZWN1cml0eVRva2VuIjoiQ0FJU2h3TjFxNkZ0NUIyeWZTaklyNWZrSVl6a3JLbHoyYlM2UlI3OXRqZHRmTHBjaVAyVHBqejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2Nsck1xRWNJWUdoeWJOWkFydE1zUHFGcnhKcExGc3QySjZyOEpqc1VRNklaZ3psbXBzdlhKYXNEVkVmbDJFNVhFTWlJUi8wMGU2TC8rY2lyWXBUWEhWYlNDbFo5Z2FQa09Rd0M4ZGtBb0xkeEtKd3hrMnQxNFVtWFdPYVNDUHdMU2htUEJMVXhtdldnR2wyUnp1NHV5M3ZPZDVoZlpwMXI4eE80YXhlTDBQb1AyVjgxbExacGxlc3FwM0k0U2M3YmFnaFpVNGdscjhxbHg3c3BCNVN5Vmt0eVdHVWhKL3phTElvaXQ3TnBqZmlCMGVvUUFQb3BGcC9YNmp2QWF3UExVbTliWXhncGhCOFIrWGo3RFpZYXV4N0d6ZW9XVE84MCthS3p3TmxuVXo5bUxMZU9WaVE0L1ptOEJQdzQ0RUxoSWFGMElVRVp6RjJ5RWNQSDVvUXFXT1YvN0ZaTG9pdjltamNCSHFIeno1c2VQS2xTMVJMR1U3RDBWSUpkVWJUbHpha2RHaFRTNUxQTmNLVklWTGc0OFd1aVBNYXgzYlFGRHI1M3ZzVGJiWHpaYjBtcHR1UG56ZDJWdWJWS1dnaytWR29BQk1HODRLVGNNMGY0dmZaSUZ1cjV2eGo0UkN3QUFpa0l0dVNaT2Y5ZFhCditxMVh1eDladVA0WjlnTm5ITzBXSUZEZGNCQ0phTTNyNFVaQUFXL25mR0FpblpjR2gxKzNqMExWaUFmQkN5a3VCQ0JGVUhqU0J4Ni9uYlRkTkhOVU13MjVLVndPN2RyMEdPd1pKd1B2MHpVaUFERmt4amNTYXU3MlFvREVqTVBOND0iLCJBdXRoSW5mbyI6IntcIkNJXCI6XCI3TDJ0V3hzejVYdDhtZFFIVlBiRVYxbUhnMFhLemFFQktKSkhBVkd1a21ZREJ1OC9RY2RNZ0JmQ0NsSkpaUTgvXCIsXCJDYWxsZXJcIjpcImZvcko3VG5wNXdkWXVQVnVvL0hOUWM5K0JXdjk5QlJ0REJYZktvcWR4amc9XCIsXCJFeHBpcmVUaW1lXCI6XCIyMDIxLTA3LTIzVDExOjUwOjIzWlwiLFwiTWVkaWFJZFwiOlwiMTNhM2JhNmQ0ZjFiNGM3YmExYjU4NWNhZDM0NDU2MmVcIixcIlBsYXlEb21haW5cIjpcImF1ZGlvdmlkZW8uYWxpLm5sc2dhcHAuY29tXCIsXCJTaWduYXR1cmVcIjpcIkV5bXptQ1MrTWVleitVd1FVR2s3Y1hQVFYrVT1cIn0iLCJWaWRlb01ldGEiOnsiU3RhdHVzIjoiTm9ybWFsIiwiVmlkZW9JZCI6IjEzYTNiYTZkNGYxYjRjN2JhMWI1ODVjYWQzNDQ1NjJlIiwiVGl0bGUiOiJ2aWRlby5tcDQiLCJDb3ZlclVSTCI6Imh0dHBzOi8vYXVkaW92aWRlby5hbGkubmxzZ2FwcC5jb20vMTNhM2JhNmQ0ZjFiNGM3YmExYjU4NWNhZDM0NDU2MmUvc25hcHNob3RzLzhmYTAyYjAxZTdjNTRlMzVhZTY2NmE3ZDM1M2VkNGZiLTAwMDA1LmpwZyIsIkR1cmF0aW9uIjo0NzUuMjY2N30sIkFjY2Vzc0tleUlkIjoiU1RTLk5UUWo2UEF2Um52UUc4TFI3OHA1cGc4OEQiLCJQbGF5RG9tYWluIjoiYXVkaW92aWRlby5hbGkubmxzZ2FwcC5jb20iLCJBY2Nlc3NLZXlTZWNyZXQiOiI3YmVUTHM2YVNBTnpKOGNOUXBYMjlRVXNWUWNFVFVpWmVmZW9CZlVRVUxHaiIsIlJlZ2lvbiI6ImNuLWJlaWppbmciLCJDdXN0b21lcklkIjoxMjU1Nzg3MDMzMjcwMTE4fQ==",
     *       "VideoMeta": {
     *          "Status": "Normal",
     *          "VideoId": "13a3ba6d4f1b4c7ba1b585cad344562e",
     *          "Title": "video.mp4",
     *          "CoverURL": "https://audiovideo.ali.nlsgapp.com/13a3ba6d4f1b4c7ba1b585cad344562e/snapshots/8fa02b01e7c54e35ae666a7d353ed4fb-00005.jpg",
     *          "Duration": 475.2666931152344
     *        },
     *      "RequestId": "5283C6AF-8C98-4434-9BE2-31280772DEC1"
     *   }
     *}
     */
    public function GetPlay(Request $request)
    {

        $params = $request->input();
        $flag = (empty($params['flag']))?0:$params['flag'];
        $videoid = (empty($params['videoid']))?'':$params['videoid'];
        $timeout = (empty($params['timeout']))?600:$params['timeout'];

        //type 1 视频 2音频
        if (!in_array($flag, [1, 2])) {
            return $this->error(0, '标记有误');
        }
        if(empty($videoid)){
            return $this->error(0, '媒体id不能为空');
        }

        try {

            self::initVodClient(self::AccessKeyId, self::AccessKeySecret);
            if($flag==1){
                //获取播放地址
                $result=$this->getPlayInfo($videoid);
            }else{
                //获取视频播放凭证
                $result=$this->getVideoPlayAuth($videoid,$timeout);
            }

            if($result['status']==1){
                return $this->success($result['data']);
            }else{
                return $this->error(0, $result['msg']);
            }

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage());
        }

    }

    /**
     * @api {post} /api/v4/upload/file_ali_oss   oss上传文件
     * @apiName file_ali_oss
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {file}  file 文件
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *      "code": 200,
     *      "msg": "成功",
     *      "now": 1627028886,
     *      "data": {
     *          "url": "https://image.nlsgapp.com/",
     *          "name": "1111/20210723d6d1d2835569399dcfcb36a2e140ac8e.doc"   //删除时传此字段值
     *      }
     *}
     */
    public function FileAliOss(Request $request)
    {

        $file=$request->file('file');
        try {
            if(!$file->isValid()){
                return $this->error(0, '文件不合法');
            }
            $fileextension = $file->getClientOriginalExtension();//获取上传文件的后缀（如abc.png，获取到的为png）
            $realpath = $file->getRealPath();//获取上传的文件缓存在tmp文件夹下的绝对路径
            $filename = $file->getClientOriginalName(); //获取上传文件的文件名（带后缀，如abc.png）
            $filesize=$file->getSize();
//            $filaname=$file->getFilename();//获取缓存在tmp目录下的文件名（带后缀，如php8933.tmp）
//            $path=$file->move(path,newname);//将缓存在tmp目录下的文件移动，返回文件移动过后的路径 第一个参数是文件移到哪个文件夹下的路径，第二个参数是将上传的文件重新命名的文件名
        }catch (\Exception $e){
            return $this->error(0, $e->getMessage());
        }
        // Endpoint以杭州为例
        $endpoint = self::EndPoint;
        // 存储空间名称
        $bucket = Config('web.Ali.BUCKET_ALI');
        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
        $filePath = $realpath;

        try{

            //上传阿里
            $ossClient = new OssClient(self::AccessKeyId, self::AccessKeySecret, $endpoint);
            // 设置文件名称
            $object = '1111group/' . date('Ymd') . md5($filename) . '.' . $fileextension;
            // 文件内容
            $doesres = $ossClient->doesObjectExist($bucket, $object); //获取是否存在
            if ($doesres) {
                return $this->error(0, '文件名已存在');
            }
            $ossClient->uploadFile($bucket, $object, $filePath);

            return $this->success([
                'url' => Config('web.Ali.IMAGES_URL'),
                'name' => $object,
                'size'=>$filesize
            ]);

        } catch(OssException $e) {
            return $this->error(0, $e->getMessage());
        }

    }

    /**
     * @api {post} /api/v4/upload/del_ali_oss   删除阿里OSS文件
     * @apiName del_ali_oss
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {string} name  文件名
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *     "code": 200,
     *     "msg": "成功",
     *     "now": 1627028907,
     *     "data": [
     *     ]
     * }
     */
    public  function DelAliOss(Request $request){

        $params = $request->input();
        $name = (empty($params['name']))?'':$params['name'];
        if(empty($name)){
            return $this->error(0, '文件名不能为空');
        }

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
                return $this->success([]);
            }else{
                return $this->error(0, '文件不存在');
            }

        } catch (OssException $e) {
            return $this->error(0, $e->getMessage());
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

    //上传完成回调
    public function Callback(Request $request){
        $params =$request->input();

        Log::channel('aliOnDemandLog')->info(json_encode($params,true));

    }

    /**
     * @api {post} /api/v4/upload/addmedia   上传成功入库
     * @apiName addmedia
     * @apiVersion 1.0.0
     * @apiGroup upload
     *
     * @apiParam {int} type   类型  1 视频 2音频 3图片 4文件
     * @apiParam {string} videoid   点播id    ||type为4不传
     * @apiParam {string} url   媒体地址  ||全链接
     * @apiParam {string} name   type为4时上传
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     * {
     *   "code": 200,
     *   "msg": "成功",
     *   "now": 1627033302,
     *   "data": {
     *
     *   }
     *}
     */
    public function AddMedia(Request $request)
    {

        $params = $request->input();
        $type = (empty($params['type']))?0:$params['type'];
        $videoid = (empty($params['videoid']))?'':$params['videoid'];
        $url = (empty($params['url']))?'':$params['url'];
        $name = (empty($params['name']))?'':$params['name'];
        $size = (empty($params['size']))?'':$params['size'];

        //type 1 视频 2音频 3 图片 4文件
        if (!in_array($type, [1, 2, 3,4])) {
            return $this->error(0, '类型有误');
        }
        if(in_array($type, [1, 2, 3])) { //类型4 文件没有资源id
            if (empty($videoid)) {
                return $this->error(0, '媒体id不能为空');
            }
        }
        if(empty($url)){
            return $this->error(0, '媒体地址不能为空');
        }
        if($type==4){
            if(empty($name)){
                return $this->error(0, '文件名不能为空');
            }
            if(empty($size)){
                return $this->error(0, '文件大小不能为空');
            }
        }

        try {
            $now_date=date('Y-m-d H:i:s');
            $data = [
                'type' => $type,
                'url' => $url,
                'created_at' => $now_date
            ];
            if(in_array($type, [1, 2, 3])) {

                self::initVodClient(self::AccessKeyId, self::AccessKeySecret);
                if($type==3) {
                    $query = [
                        'ImageId' => $videoid,
                    ];
                    $action='GetImageInfo';
                }else{
                    $query=[
                        'VideoId' => $videoid,
                    ];
                    $action="GetVideoInfo";
                }
                $ruselt=self::AlibabaCloudRpcRequest($action,$query);
                if($ruselt['status']!=1){
                    return $this->error(0, '获取保存失败');
                }
                if($type==1){ //视频
                    $data['size']=(empty($ruselt['data']['Video']['Size']))?'':$ruselt['data']['Video']['Size'];
                    $data['second']=(empty($ruselt['data']['Video']['Duration']))?'':$ruselt['data']['Video']['Duration'];
                    $arr=explode('.',$url);
                    $ext=$arr[count($arr)-1]; //扩展名
                    $data['format']=$ext;
                    $data['thumb_url']=(empty($ruselt['data']['Video']['CoverURL']))?'':$ruselt['data']['Video']['CoverURL'];
//                    $data['thumb_size']=;
//                    $data['thumb_width']=;
//                    $data['thumb_height']=;
                    if(!empty($data['thumb_url'])){
                        $thumb_arr=explode('.',$data['thumb_url']);
                        $thumb_ext=$thumb_arr[count($thumb_arr)-1]; //扩展名
                        $data['thumb_format']=$thumb_ext;
                    }
                }else if($type==2){ //音频
                    $data['size']=(empty($ruselt['data']['Video']['Size']))?'':$ruselt['data']['Video']['Size'];
                    $data['second']=(empty($ruselt['data']['Video']['Duration']))?'':$ruselt['data']['Video']['Duration'];
                    $arr=explode('.',$url);
                    $ext=$arr[count($arr)-1]; //扩展名
                    $data['format']=$ext;
                }else{ //图片
                    $data['size']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['FileSize']))?'':$ruselt['data']['ImageInfo']['Mezzanine']['FileSize'];
                    $data['width']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['Width']))?'':$ruselt['data']['ImageInfo']['Mezzanine']['Width'];
                    $data['height']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['Height']))?'':$ruselt['data']['ImageInfo']['Mezzanine']['Height'];
                    $arr=explode('.',$url);
                    $ext=$arr[count($arr)-1]; //扩展名
                    $data['format']=$ext;
                }

                $data['media_id'] = $videoid;
                $rst = DB::table(ImMedia::DB_TABLE)->insert($data);
                if ($rst === false) {
                    return $this->error(0, '保存失败');
                }
            }else{ //文件
                DB::beginTransaction();
                $data['file_name'] = $name;
                $data['size'] = $size;
                $rstId = DB::table(ImMedia::DB_TABLE)->insertGetId($data);
                if ($rstId === false) {
                    DB::rollBack();
                    return $this->error(0, '保存失败');
                }
                //初始化媒体id
                $UpRst=DB::table(ImMedia::DB_TABLE)->where('id', $rstId)->update(['media_id' => $rstId,'updated_at' => $now_date]);
                if($UpRst===false){
                    DB::rollBack();
                    return $this->error(0, '保存失败');
                }
                DB::commit();
            }

            return $this->success([]);

        } catch (\Exception $e) {
            if($type==4){
                DB::rollBack();
            }
            return $this->error(0, $e->getMessage());
        }

    }


    //定时抓取腾讯IM音视频、图片、文件到阿里云平台
    public  function TimingGrab(Request $request){
        $params = $request->input();
        $type = (empty($params['type']))?0:$params['type'];

        //type 1 视频 2音频 3 图片 4文件
        if (!in_array($type, [1, 2,3,4])) {
            return $this->error(0, '抓取类型有误');
        }

        try {

            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/7bc3-233664/c429a6ec8b00ac994bff2579620799a6-342940?imageMogr2/';
            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/be3a-166788/46d7b74f9a396ce76539c1c8f8295b44.png?imageMogr2/';
            $url= 'https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/1a0aa9b22d14de0f63e16173a5ad955a.png';
            //抓取音视频和图片
            $url= 'https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/cca8979fd71055639f493a914979c361?imageView2/3/w/198/h/198';
            $result = $this->UploadMediaByURL(3,$url);
            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/eaf5-318699/a26bdb7e80107460cad35cad17c20f18.mp4';
            $result = $this->UploadMediaByURL(1,$url);
            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/29845510b9fe73f1ea290b7c2466277d.m4a';
            $result = $this->UploadMediaByURL(2,$url);
            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/f866-316743/7eca6acb927a86ba8580c8e0ce83ac84.txt';
            $result = $this->UploadMediaByURL(4,$url);

            if($result['status']==1){
                return $this->success($result['data']);
            }else{
                return $this->error(0, $result['msg']);
            }

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage());
        }

    }

    //音视频拉取文件 https://help.aliyun.com/document_detail/100976.html?spm=a2c4g.11186623.6.1031.30f6d418f1Hpzw
    public function UploadMediaByURL($type,$url){

        if(in_array($type,[1,2,3])){
            require_once base_path('vendor').DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'Autoloader.php';
            date_default_timezone_set('PRC');
        }
        $now_date=date('Y-m-d H:i:s');
        if (in_array($type,[1,2])) { //拉取音视频
            $arr=explode('.',$url);
            $filename=md5($url); //文件名
            $ext=$arr[count($arr)-1]; //扩展名
            $filePath=storage_path('logs/'.$filename.'.'.$ext);
            if(!file_exists($filePath)) {
                try {
                    file_put_contents($filePath, file_get_contents($url)); //远程下载文件到本地
                } catch (\Exception $e) {
                    return ['status' => 0, 'data' => [], 'msg' => $url . '下载异常：' . $e->getMessage()];
                }
            }

            $uploader = new \AliyunVodUploader(self::AccessKeyId, self::AccessKeySecret,'cn-beijing');
            $uploadVideoRequest = new \UploadVideoRequest($filePath, '测试上传视频');
            $uploadVideoRequest->setCateId(self::TypeArr[$type]);
            $uploadVideoRequest->setStorageLocation(self::StorageLocation);
            if($type==1) { //视频
                $uploadVideoRequest->setWorkflowId(self::WorkflowId);
            }
//            $userData = array(
//                "MessageCallback"=>array("CallbackURL"=>"http://app.v4.apitest.nlsgapp.com/api/v4/upload/callback"),
//                "Extend"=>array("localId"=>"xxx", "test"=>"www")
//            );
//            $uploadVideoRequest->setUserData(json_encode($userData));
            $videoid = $uploader->uploadLocalVideo($uploadVideoRequest);
            $new_url='';
        }else if($type==3){//拉取图片

            $filename=md5($url); //文件名
            $ext='png'; //扩展名
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
            $uploadImageRequest = new \UploadImageRequest($filePath, '测试图片上传');
            $uploadImageRequest->setCateId(self::TypeArr[3]);
            $res = $uploader->uploadLocalImage($uploadImageRequest);
            $new_url=$res['ImageURL'];
            $videoid=$res['ImageId']; //媒体id

        }
        if(in_array($type,[1,2,3])){
            $data = [
                'type' => $type,
                'url' => $new_url,
                'created_at' => $now_date
            ];
            $data['media_id'] = $videoid;
            $rst = DB::table(ImMedia::DB_TABLE)->insert($data);
            if ($rst === false) {
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败'];
            }else{
                unlink($filePath);
                return ['status' => 1, 'data' => [], 'msg' => '抓取成功'];
            }
        }
        if($type==4) {//拉取文件上传oss
            return self::GetUrlOSS($url);
        }

    }

    //腾讯下载文件上传OSS
    public function GetUrlOSS($url){

        $arr=explode('.',$url);
        $filename=md5($url); //文件名
        $ext=$arr[count($arr)-1]; //扩展名

        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
        $filePath=storage_path('logs/'.$filename.'.'.$ext);
        if(!file_exists($filePath)) {
            try {
                file_put_contents($filePath, file_get_contents($url)); //远程下载文件到本地
            } catch (\Exception $e) {
                return ['status' => 0, 'data' => [], 'msg' => $url . '下载异常：' . $e->getMessage()];
            }
        }

        // Endpoint以杭州为例
        $endpoint = self::EndPoint;
        // 存储空间名称
        $bucket = Config('web.Ali.BUCKET_ALI');

        $now_date=date('Y-m-d H:i:s');
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

            DB::beginTransaction();
            $data = [
                'type' => 4,
                'url' => self::$IMAGES_URL.$object,
                'file_name' => $object,
                'created_at' => $now_date
            ];
            $rstId = DB::table(ImMedia::DB_TABLE)->insertGetId($data);
            if ($rstId === false) {
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败111'];
            }
            //初始化媒体id
            $UpRst=DB::table(ImMedia::DB_TABLE)->where('id', $rstId)->update(['media_id' => $rstId,'updated_at' => $now_date]);
            if($UpRst===false){
                DB::rollBack();
                return ['status' => 0, 'data' => [], 'msg' => '抓取失败222'];
            }
            DB::commit();
            unlink($filePath);
            return ['status' => 1, 'data' => [], 'msg' => '抓取成功'];

        } catch(OssException $e) {
            return [ 'status' => 0,'data'=>[],'msg'=>$e->getMessage()];
        }

    }


}
