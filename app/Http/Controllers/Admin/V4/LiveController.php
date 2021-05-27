<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveInfo;
use App\Models\LivePush;
use App\Models\Wiki;
use App\Models\WorksInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveController extends Controller
{

    /**
     * @api {get} api/admin_v4/live/index 直播列表
     * @apiVersion 4.0.0
     * @apiName   live/index
     * @apiGroup 后台-直播列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/live/index
     * @apiDescription 直播列表
     *
     * @apiParam {number} page 分页
     *
     * @apiSuccess {string} title    标题
     * @apiSuccess {array}  price    价格
     * @apiSuccess {number} is_finish 是否结束  1 是0 否
     * @apiSuccess {number} status    直播状态 1:待审核  2:已取消 3:已驳回  4:通过
     * @apiSuccess {number} created_at  创建时间
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function index()
    {
        $lists = Live::select('id', 'user_id', 'title', 'price', 'is_finish', 'finished_at', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }


    /**
     * 审核直播
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\JsonResponsew
     */
    public function pass(Request $request)
    {
        $id = $request->get('id');
        $res = Live::where('id', $id)
            ->update([
                'status'     => 4,
                'check_time' => date('Y-m-d H:i:s'),
            ]);
        if ($res) {
            return success();
        }
        return error(1000, '审核失败');
    }


    public function push(Request $request)
    {
        $live_id  = $request->get('live_id');
        $query = LivePush::when($live_id, function ($query) use ($live_id) {
                $query->where('live_id', $live_id);
            });

        $lists = $query->select('id', 'live_id', 'push_type', 'push_gid', 'is_push', 'is_done', 'push_at')
            ->orderBy('push_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists){
            $push = LivePush::parsePushList($lists['data']);
        }
        return success($push);
    }

    public  function  create(Request $request)
    {
        $id     = $request->get('id');
        $type   = $request->get('type');
        $relationId = $request->get('relation_id');
        $liveId = $request->get('live_id');

        $data = [
            'live_id'   => $liveId,
            'push_type' => $type,
            'push_gid'  => $relationId,
            'is_push'   => 1,
            'is_done'   => 1,
            'push_at'   => date('Y-m-d H:i:s', time()),
            'done_at'   => date('Y-m-d H:i:s', time())
        ];
        if (!empty($id)) {
            LivePush::where('id', $id)->update($data);
        } else {
            LivePush::create($data);
        }
        return success();
    }

    public  function delete(Request $request)
    {
        $id = $request->get('id');
        $res = LivePush::where('id', $id)->delete();
        if ($res){
            return success('删除成功');
        }

        return error(1000,'删除失败');
    }

    public function begin(Request $request){
        $model = new LiveConsole();
        $res = $model->begin($request->input());
        return $this->getRes($res);
    }





    /**
     * 直播自动拉流任务
     */
    public function livePushUrlCreate(Request $request){

        $type = $request->input("type") ?? '';
        $live_info_id = $request->input("live_info_id") ?? '';

        if($type == "" || $live_info_id == '' ){
            return error(0, 'error');
        }
        //开始时间   结束时间    pushurl   callbackurl
        $info = LiveInfo::find($live_info_id);
        if($type == 'create' && !empty($info['task_id'])){
            return error(0, '已创建拉流任务');
        }

        if($type == 'del' && empty($info['task_id'])){
            return error(0, '当前拉流任务不存在');
        }


        $subject        = $info['push_live_url'];
        $playback_url   = $info['playback_url'];
        $str_time       = strtotime($info['begin_at'])+5;  //开始时间需要大于当前时间  多加5秒
        $end_time       = $str_time+3600*3;//  结束时间需要大于当前时间

        if( $str_time <= time() ){
            return error(0, '直播开始时间必须大于当前时间');
        }

        $SecretId="AKIDrcCpIdlpgLo4A4LMj7MPFtKfolWeNHnC";
        $SECRET_KEY="MWXLwKVXMzPcrwrcDcrulPsAF7nIpCNM";
        //加密
        $rand = rand (100, 10000000); //9031868223070871051
        $time = time ();
        $Region = "ap-guangzhou";
        $data_key = [
            'Action' => 'DescribeLivePullStreamTasks',
            'Version' => "2018-08-01",
            'Region' => $Region,
            'SecretId' => $SecretId,
            'Timestamp' => $time,
            'Nonce' => $rand,
            'SignatureMethod' => 'HmacSHA256',
        ];

        switch ($type){
            case "create":

                $pattern_1 = '/rtmp:\/\/push.live.nlsgapp.com\/live\/(.*?)\?txSecret=(.*?)/';
                $num = preg_match_all($pattern_1, $subject, $matches_1,PREG_PATTERN_ORDER);
                if( $num <= 0 ){
                    break;
                }
                $StreamName = $matches_1[1][0];
                $data_key['Action'] = 'CreateLivePullStreamTask';
                $data_key['Version'] = '2018-08-01';
                $data_key['SourceType'] = 'PullVodPushLive'; //点播 类型
                //$data_key['SourceUrls.0'] = 'http://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/d590feb55285890818716274924/Ja0YTxwJYVIA.mp4';
                $data_key['SourceUrls.0'] = $playback_url;
                $data_key['DomainName'] = 'push.live.nlsgapp.com'; //推流域名
                $data_key['AppName'] = 'live';  //推流路径。
                $data_key['StreamName'] = $StreamName; //推流名称。。
                // 北京时间值 = UTC 时间值 + 8 小时

                $data_key['StartTime'] = date('Y-m-d\TH:i:s.0000\Z', $str_time-28800); //开始时间  使用 UTC 格式时间，北京时间值 = UTC 时间值 + 8 小时
                $data_key['EndTime'] = date('Y-m-d\TH:i:s.0000\Z', $end_time-28800); //结束时间  使用 UTC 格式时间结束时间 和 开始时间 间隔必须小于七天。 使用 UTC 格式时间，
                $data_key['Operator'] = 'admin'; //任务创建人。

                break;
            case "show":
                $data_key['Action'] = 'DescribeLivePullStreamTasks';
                break;
            case "del":



                if($info['task_id'] == ''){
                    return error(0, 'error');
                }
                $data_key['Action'] = 'DeleteLivePullStreamTask';
                $data_key['TaskId'] = $info['task_id'];//任务 Id。  创建时返回的任务id
                $data_key['Operator'] = 'admin'; //任务创建人。
                break;
            default:
                $data_key['Action'] = 'DescribeLivePullStreamTasks';
                break;
        }
        ksort ($data_key); //排序
        // 计算签名
//        $srcStr = "POSTlive.tencentcloudapi.com/?" . http_build_query ($data_key);
        //使用这种方式 是为了避免特殊字符转义 导致签名失败
        $srcStr = "POSTlive.tencentcloudapi.com/?";
        foreach ( $data_key as $key => $value ) {
            $srcStr = $srcStr . $key . "=" . $value . "&";
        }
        $srcStr = substr($srcStr, 0, -1);


        $signature = base64_encode (hash_hmac ('sha256', $srcStr, $SECRET_KEY, true)); //SHA1  sha256
        $data_key['Signature'] = $signature;
        ksort ($data_key); //排序



        //拉取转码成功信息
        $url = "https://live.tencentcloudapi.com/";
        $info = WorksInfo::curlPost ($url, $data_key);  //post
        $raw_array = json_decode($info, true);

        if(empty($raw_array['Response']['Error']) ){

            switch ($type){
                case "create":
                    //TaskId 记录数据库
                    LiveInfo::where('id', $live_info_id)->update([
                        'task_id' => $raw_array['Response']['TaskId'],
                    ]);
                    $res = $raw_array['Response'];
                    break;
                case "show":
                    $res = $raw_array['Response']['TaskInfos'];
                    break;
                case "del":
                    LiveInfo::where('id', $live_info_id)->update([
                        'task_id' => 0,
                    ]);
                    $res = $raw_array['Response'];
                    break;
                default:
                    $res = [];
                    break;
            }
        }else{
            return error(0, 'error',$raw_array['Response']['Error']);
        }
        return $this->success($res);

    }
}
