<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Live;
use App\Models\LiveInfo;
use Carbon\Carbon;

class LiveController extends Controller
{
    /**
     * @api {get} api/v4/live/index  直播首页
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Live
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/index
     *
     * @apiSuccess {array} live_lists 直播列表
     * @apiSuccess {array} live_lists.title 直播标题
     * @apiSuccess {array} live_lists.price 直播价格
     * @apiSuccess {array} live_lists.cover_img 直播封面
     * @apiSuccess {array} live_lists.type 直播类型 1单场 2多场
     * @apiSuccess {array} live_lists.user 直播用户信息
     * @apiSuccess {array} live_lists.live_time 直播时间
     * @apiSuccess {array} live_lists.live_status 直播状态
     * @apiSuccess {array} back_lists 回放列表
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                "data": {
             "live_lists": [
                 {
                     "id": 136,
                     "user_id": 161904,
                     "title": "测试57",
                     "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
                     "price": "0.00",
                     "cover_img": "/nlsg/works/20200611095034263657.jpg",
                     "begin_at": "2020-10-01 15:02:00",
                     "type": 1,
                     "user": {
                         "id": 161904,
                         "nickname": "王琨"
                     },
                     "live_time": "2020.10.01 15:02",
                     "live_status": "正在直播"
                 }
             ],
             "back_lists": [
                 {
                     "id": 136,
                     "user_id": 161904,
                     "title": "测试57",
                     "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
                     "price": "0.00",
                     "cover_img": "/nlsg/works/20200611095034263657.jpg",
                     "begin_at": "2020-10-01 15:02:00",
                     "type": 1,
                     "user": {
                         "id": 161904,
                         "nickname": "王琨"
                     },
                     "live_time": "2020.10.01 15:02"
                 },
                 {
                     "id": 137,
                     "user_id": 255446,
                     "title": "测试",
                     "describe": "测试",
                     "price": "1.00",
                     "cover_img": "/nlsg/works/20200611172548507266.jpg",
                     "begin_at": "2020-10-01 15:02:00",
                     "type": 1,
                     "user": null,
                     "live_time": "2020.10.01 15:02"
                 }
             ]
         }
     *         ]
     *     }
     *
     */
    public function  index()
    {
        $liveLists =  Live::with('user:id,nickname')
                   ->select('id','user_id','title', 'describe','price','cover_img','begin_at','type','end_at')
                   ->where('status', 4)
                   ->orderBy('begin_at','desc')
                   ->limit(3)
                   ->get()
                   ->toArray();
        if (!empty($liveLists)){
            foreach ($liveLists as &$v) {
                if (strtotime($v['end_at']) < time()) {
                   $v['live_status'] = '已结束';
                } else {
                   $v['live_status'] = '正在直播';
                }
                $v['live_time'] =  date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        $backLists =  Live::with('user:id,nickname')
                    ->select('id','user_id','title', 'describe','price','cover_img','begin_at','type')
                    ->where('end_at', '>' , Carbon::now()->toDateTimeString())
                    ->where('status', 4)
                    ->orderBy('created_at', 'desc')
                    ->limit(2)
                    ->get()
                    ->toArray();
        if (!empty($backLists)){
              foreach ($backLists as &$v) {
                  $v['live_time'] =  date('Y.m.d H:i', strtotime($v['begin_at']));
              }
      }
        $data  = [
            'live_lists' =>  $liveLists,
            'back_lists' =>  $backLists
        ];
        return success($data);
    }

    /**
     * 重置直播类型
     */
    public  function  reLiveType()
    {
        $liveLists = Live::orderBy('begin_at','desc')
                      ->get()
                      ->toArray();
        if ($liveLists){
            foreach ($liveLists as $v) {
                $count = LiveInfo::where('live_pid', $v['id'])->count();
                $type = $count > 1 ? 2 : 1;
                Live::where('id', $v['id'])->update([
                    'type' => $type
                ]);
            }
        }
        return;
    }
}
