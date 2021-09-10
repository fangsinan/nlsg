<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImMedia;
use App\Servers\AliUploadServers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

//https://help.aliyun.com/document_detail/123461.html?spm=a2c4g.11186623.6.1074.525958a4xbtMvZ      安装php -d memory_limit=-1 composer.phar require alibabacloud/sdk
//https://next.api.aliyun.com/api/vod/2017-03-21/GetPlayInfo?params={%22VideoId%22:%2213a3ba6d4f1b4c7ba1b585cad344562e%22}&sdkStyle=old    接口调试
class AliUploadController extends Controller
{

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

            $AliUploadServer=new AliUploadServers();
            $AliUploadServer->initVodClient();

            if(in_array($type,[1,2])) {
                //获取视频上传地址和凭证
                $result = $AliUploadServer->createUploadVideo($type, $filename, $title);
            }else {
                //图片上传凭证
                $result = $AliUploadServer->createUploadImage($type, $title, $imageext);
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

            $AliUploadServer=new AliUploadServers();
            $AliUploadServer->initVodClient();
            if(in_array($type,[1,2])) {
                //删除时修改类型
                $result = $AliUploadServer->updateVideoInfo($videoid);
            }else{
                //删除图片
                $result=$AliUploadServer->DeleteImage($videoid);
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

            $AliUploadServer=new AliUploadServers();
            $AliUploadServer->initVodClient();
            if($flag==1){
                //获取播放地址
                $result=$AliUploadServer->getPlayInfo($videoid);
            }else{
                //获取视频播放凭证
                $result=$AliUploadServer->getVideoPlayAuth($videoid,$timeout);
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
            $filePath = $file->getRealPath();//获取上传的文件缓存在tmp文件夹下的绝对路径
            $filename = $file->getClientOriginalName(); //获取上传文件的文件名（带后缀，如abc.png）
            $filesize=$file->getSize();
            $maxSize=1024*1024*50;
            if($filesize>$maxSize){
                return $this->error(0, '上传文件超过50M', (object)[]);
            }
//            $filaname=$file->getFilename();//获取缓存在tmp目录下的文件名（带后缀，如php8933.tmp）
//            $path=$file->move(path,newname);//将缓存在tmp目录下的文件移动，返回文件移动过后的路径 第一个参数是文件移到哪个文件夹下的路径，第二个参数是将上传的文件重新命名的文件名
        }catch (\Exception $e){
            return $this->error(0, $e->getMessage());
        }

        $len= strlen($fileextension)+1;
        $filename= substr($filename,0,-$len); //去掉扩展名

        $AliUploadServer=new AliUploadServers();
        $RstData=$AliUploadServer->PushOSS($filename,$fileextension,$filePath);
        if($RstData['status']==1){
            $RstData['data']['size']=$filesize; //返回大小
            return $this->success($RstData['data']);
        }else{
            return $this->error(0, $RstData['msg'], (object)[]);
        }

    }

    //OSS文件上传web端sts
    public  function FileAliOssSts(Request $request){

        $params = $request->input();
        $type = (empty($params['type']))?0:$params['type'];

        $AliUploadServer=new AliUploadServers();
        $RstData=$AliUploadServer->FileAliOssSts();
        if($RstData['status']==1){
            return $this->success($RstData['data']);
        }else{
            return $this->error(0, $RstData['msg'], (object)[]);
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

        $AliUploadServer=new AliUploadServers();
        $Rst=$AliUploadServer->DelOss($name);
        if($Rst['status']==1){
            return $this->success([]);
        }else{
            return $this->error(0, $Rst['msg']);
        }
    }

    //上传完成回调
    public function Callback(Request $request){
        $data =$request->input();

        if(!empty($data)){
            Log::channel('aliOnDemandLog')->info(json_encode($data,true));
            if(!empty($data['Status']) && $data['Status']=='success'){
                $map=[];
                if(!empty($data['Extend'])) { //有返回值
                    $returnArr = json_decode($data['Extend'], true);
                    if (!empty($returnArr['type']) && $returnArr['type'] == 2) { //音频 1s
                        $map['second'] = $data['Duration']; //时长
                        $map['size'] = $data['Size']; //大小
                        $map['is_finish']=1;
                    } else if (!empty($returnArr['type']) && $returnArr['type'] == 1) { //处理视频
                        if(!empty($data['EventType']) && $data['EventType']=='VideoAnalysisComplete'){ //视频分析完成 1s
                            $map['second'] = $data['Duration']; //时长
                        }else if(!empty($data['EventType']) && $data['EventType']=='SnapshotComplete' && !empty($data['CoverUrl'])) { //封面图  此回调相对较长3-4s
                            $map['thumb_url'] = str_replace("https://","http://",$data['CoverUrl']);
                            $CoverSize = getimagesize($map['thumb_url']);
                            $map['thumb_width'] = $CoverSize[0];
                            $map['thumb_height'] = $CoverSize[1];
//                            $data['thumb_size']=;
                            $thumb_arr = explode('.', $map['thumb_url']);
                            $thumb_ext = $thumb_arr[count($thumb_arr) - 1]; //扩展名
                            $map['thumb_format'] = $thumb_ext;

                            $map['media_id'] = $data['VideoId'];//媒体id
                            $map['is_finish'] = 1;
                        }else{
                            return true;
                        }
                    }

                    $ImMediaInfo = ImMedia::query()->where('media_id', $data['VideoId'])->first();
                    if (empty($ImMediaInfo)) {
                        $rst = DB::table(ImMedia::DB_TABLE)->insert($map);
                    } else {
                        $rst = DB::table(ImMedia::DB_TABLE)->where('id', $ImMediaInfo->id)->update($map);
                    }
                    if ($rst === false) {
                        Log::channel('aliOnDemandLog')->info($data['VideoId'].' Callback fail');
                    }
                }

            }
        }

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
                'url' => str_replace("https://","http://",$url),
                'created_at' => $now_date
            ];
            if(in_array($type, [1, 2, 3])) {

                $AliUploadServer=new AliUploadServers();
                $AliUploadServer->initVodClient();
                if($type==1){ //视频
                    $query=[
                        'VideoId' => $videoid,
                    ];
                    $action="GetVideoInfo";
                }else if($type==2){  //音频
                    $query=[
                        'VideoId' => $videoid,
                    ];
                    $action="GetMezzanineInfo"; //查询源文件信息
                }else if($type==3) {
                    $query = [
                        'ImageId' => $videoid,
                    ];
                    $action='GetImageInfo';
                }
                $ruselt=$AliUploadServer->AlibabaCloudRpcRequest($action,$query);

                if($ruselt['status']!=1){
                    return $this->error(0, '获取保存失败');
                }
                $arr=explode('.',$url);
                $ext=$arr[count($arr)-1]; //扩展名
                $data['format']=$ext;
                if($type==1){ //视频
//                    $arrLog=json_encode($ruselt['data']['Video'],true);
//                    Log::channel('aliOnDemandLog')->info("--video-AddMedia---".$arrLog);
                    $data['size']=(empty($ruselt['data']['Video']['Size']))?0:$ruselt['data']['Video']['Size'];
//                    $data['second']=(empty($ruselt['data']['Video']['Duration']))?0:$ruselt['data']['Video']['Duration']; //回调已处理
                    $data['file_name']=(empty($ruselt['data']['Video']['Title']))?'':$ruselt['data']['Video']['Title'];
                }else if($type==2){ //音频
                    $data['size']=(empty($ruselt['data']['Mezzanine']['Size']))?0:$ruselt['data']['Mezzanine']['Size'];
//                    $data['second']=(empty($ruselt['data']['Mezzanine']['Duration']))?0:$ruselt['data']['Mezzanine']['Duration']; //回调已处理
                    $data['file_name']=(empty($ruselt['data']['Mezzanine']['FileName']))?'':$ruselt['data']['Mezzanine']['FileName'];

                }else{ //图片
                    $url_height=$url_width=0;
                    if(empty($ruselt['data']['ImageInfo']['Mezzanine']['Width']) || empty($ruselt['data']['ImageInfo']['Mezzanine']['Height'])){
                        $UrlImage = getimagesize($data['url']);
                        $url_width = $UrlImage[0];
                        $url_height = $UrlImage[1];
                    }
                    $data['file_name']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['OriginalFileName']))?'':$ruselt['data']['ImageInfo']['Mezzanine']['OriginalFileName'];
                    $data['size']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['FileSize']))?0:$ruselt['data']['ImageInfo']['Mezzanine']['FileSize'];
                    $data['width']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['Width']))?$url_width:$ruselt['data']['ImageInfo']['Mezzanine']['Width'];
                    $data['height']=(empty($ruselt['data']['ImageInfo']['Mezzanine']['Height']))?$url_height:$ruselt['data']['ImageInfo']['Mezzanine']['Height'];
                    $data['is_finish']=1;
                }

                $data['media_id'] = $videoid;
                if($type==3 && $type==2){//图片
                    $rst = DB::table(ImMedia::DB_TABLE)->insert($data);
                }else {//视频
                    $ImMediaInfo = ImMedia::query()->where('media_id', $videoid)->first();
                    if (empty($ImMediaInfo)) {
                        $rst = DB::table(ImMedia::DB_TABLE)->insert($data);
                    } else {
                        $rst = DB::table(ImMedia::DB_TABLE)->where('id', $ImMediaInfo->id)->update($data);
                    }
                }
                if($rst===false){
                    DB::rollBack();
                    return $this->error(0, '保存失败');
                }
                return $this->success(['videoid'=>$videoid]);
             }else{ //文件
                DB::beginTransaction();
                $data['file_name'] = $name;
                $data['size'] = $size;
                $rstId = DB::table(ImMedia::DB_TABLE)->insertGetId($data);
                if (!$rstId || $rstId===false) {
                    DB::rollBack();
                    return $this->error(0, '保存失败');
                }
                //初始化媒体id
                $UpRst=DB::table(ImMedia::DB_TABLE)->where('id', $rstId)->update(['media_id' => $rstId,'updated_at' => $now_date]);
                if(!$UpRst || $UpRst===false){
                    DB::rollBack();
                    return $this->error(0, '保存失败');
                }
                DB::commit();
                return $this->success(['videoid'=>$rstId]);
            }

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
            $ImgType=getimagesize($url);
            var_dump($ImgType);
            return ;
            /*$map=[];
            for($n=1;$n<=100;$n++){

//                $str=18522222;
                $str=18512342;
//                $code = str_pad($n,3,0,STR_PAD_LEFT);
                $code=300+$n;
                $code=260+$n;
                $phone=$str.$code;
                $map[]=[
                    'phone' => $phone,
//                    'is_qd_push' => 1,#地推
                    'is_qd_push' => 2,#李婷
                    'nickname' => substr_replace($phone, '****', 3, 4),
                    'created_at'=>date('Y-m-d H:i:s')
                ];
            }
            $rst = DB::table('nlsg_user')->insert($map);
            var_dump($rst);
            return ;*/

            $AliUploadServer=new AliUploadServers();

//            $rst=$AliUploadServer->UploadMediaPull();
//           var_dump($rst);
//           return ;
//           $rst=$AliUploadServer->UploadMediaVideoAudio();
//           var_dump($rst);
//           return ;

            $AliUploadServer->initVodClient();
            //处理客户端上传拿不到宽高截图
            $videoid='d7485a804e3c4c82952ce06a85614af5'; //控制台传mp3
//            $videoid='b8f021293bc744718532f6d94da12909'; //控制台传mp4   状态完成 有转码

            $videoid='adf41759727f47469565631aedc1fdc6';//客户端m4a
            $videoid='2ecb28e5d65e46d294bad2b28231d07e';//客户端mp4  状态 上传完成  未转码

//            $videoid='ae6dce079da246f98edfb8b00add0370'; //mp4
            $videoid='c63ef8f65b9f48708a65276154060ddd';//m4a

            //查询信息
            //https://help.aliyun.com/document_detail/56124.htm?spm=a2c4g.11186623.2.6.386e7d44cGAnIu#doc-api-vod-GetPlayInfo
            $query=[
                'VideoId' => $videoid,
            ];
//            $result=$AliUploadServer->AlibabaCloudRpcRequest('GetPlayInfo',$query); //获取播放地址 只支持mp4  m3u8  mp3  mpd    但是如果状态不是正常状态只是上传完成状态也不能获取
//            echo '播放地址：'.PHP_EOL;
//            var_dump($result);
//            $result=$AliUploadServer->AlibabaCloudRpcRequest('GetVideoPlayAuth',$query);//获取播放凭证
//            echo '播放凭证：'.PHP_EOL;
//            var_dump($result);
//            $result=$AliUploadServer->AlibabaCloudRpcRequest('GetVideoInfo',$query);
//            echo '媒体信息：'.PHP_EOL;
//            var_dump($result);
            $result=$AliUploadServer->AlibabaCloudRpcRequest('GetMezzanineInfo',$query);
            echo '原始文件：'.PHP_EOL;
            var_dump($result['data']);
            return ;
            $url1='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/7bc3-233664/c429a6ec8b00ac994bff2579620799a6-342940?imageMogr2/';
            $url2='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/be3a-166788/46d7b74f9a396ce76539c1c8f8295b44.png?imageMogr2/';
            $url3= 'https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/1a0aa9b22d14de0f63e16173a5ad955a.png';
            //抓取音视频和图片
            $url4= 'https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/cca8979fd71055639f493a914979c361?imageView2/3/w/198/h/198';
            $result = $AliUploadServer->UploadMediaByURL(3,$url1);
            $result = $AliUploadServer->UploadMediaByURL(3,$url2);
            $result = $AliUploadServer->UploadMediaByURL(3,$url3);
            $result = $AliUploadServer->UploadMediaByURL(3,$url4);


            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/eaf5-318699/a26bdb7e80107460cad35cad17c20f18.mp4';
            $result = $AliUploadServer->UploadMediaByURL(1,$url);
//            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a18b-318504/29845510b9fe73f1ea290b7c2466277d.m4a';
//            $result = $AliUploadServer->UploadMediaByURL(2,$url);
//            $url='https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/f866-316743/7eca6acb927a86ba8580c8e0ce83ac84.txt';
//            $result = $AliUploadServer->UploadMediaByURL(4,$url);

            if($result['status']==1){
                return $this->success($result['data']);
            }else{
                return $this->error(0, $result['msg']);
            }

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage().' '.$e->getLine());
        }

    }

}
