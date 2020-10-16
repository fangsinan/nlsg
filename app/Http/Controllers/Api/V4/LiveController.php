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
     * @api {get} api/v4/live/lists  直播更多列表
     * @apiVersion 4.0.0
     * @apiName  lists
     * @apiGroup Live
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/lists
     * @apiParam  {number}  page  分页
     *
     * @apiSuccess {string}  title 同直播首页返回值
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                {
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
     *         ]
     *     }
     *
     */
    public  function  getLiveLists()
    {
        $lists =  Live::with('user:id,nickname')
                           ->select('id','user_id','title', 'describe','price','cover_img','begin_at','type','end_at')
                           ->where('status', 4)
                           ->orderBy('begin_at','desc')
                           ->paginate(10)
                           ->toArray();
        if (!empty($lists['data'])){
            foreach ($lists['data'] as &$v) {
                if (strtotime($v['begin_at']) > time()) {
                   $v['live_status'] = '未开始';
                } else {
                    if (strtotime($v['end_at']) < time()) {
                        $v['live_status'] = '已结束';
                    } else {
                        $v['live_status'] = '正在直播';
                    }
                }

                $v['live_time'] =  date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        return success($lists['data']);
    }

    /**
     * @api {get} api/v4/live/back_lists  回放更多列表
     * @apiVersion 4.0.0
     * @apiName  back_lists
     * @apiGroup Live
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/back_lists
     * @apiParam  {number}  page  分页
     *
     * @apiSuccess {string}  title 同直播首页返回值
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                {
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
                      }
     *         ]
     *     }
     *
     */
    public  function  getLiveBackLists()
    {
        $lists =  Live::with('user:id,nickname')
                    ->select('id','user_id','title', 'describe','price','cover_img','begin_at','type')
                    ->where('end_at', '>' , Carbon::now()->toDateTimeString())
                    ->where('status', 4)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
        if (!empty($lists['data'])){
            foreach ($lists['data'] as &$v) {
              $v['live_time'] =  date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }
        return $lists['data'];
    }

    /**
     * @api {get} api/v4/live/channels  直播场次列表
     * @apiVersion 4.0.0
     * @apiName  channels
     * @apiGroup Live
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/channels
     * @apiParam {number} id  直播期数id
     *
     * @apiSuccess {string} live_time    直播时间
     * @apiSuccess {string} live_status  直播状态
     * @apiSuccess {string} user         直播用户
     * @apiSuccess {string} live         直播相关
     * @apiSuccess {string} live.title   直播标题
     * @apiSuccess {string} live.price   直播价格
     * @apiSuccess {string} live.cover_img   直播封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
                         "id": 11,
                         "user_id": 161904,
                         "live_pid": 1,
                         "begin_at": "2020-10-17 10:00:00",
                         "end_at": null,
                         "user": {
                             "id": 161904,
                             "nickname": "王琨"
                         },
                         "live": {
                             "id": 1,
                             "title": "第85期《经营能量》直播",
                             "price": "0.00",
                             "cover_img": "/live/look_back/live-1-9.jpg"
                         },
                         "live_status": "未开始",
                         "live_time": "2020.10.17 10:00"
                     }
     *         ]
     *     }
     *
     */
    public  function  getLiveChannel(Request $request)
    {
        $id =  $request->get('id');
        $lists =  LiveInfo::with(['user:id,nickname','live:id,title,price,cover_img'])
                      ->select('id','user_id','live_pid','begin_at','end_at')
                      ->where('status', 4)
                      ->where('live_pid', $id)
                      ->orderBy('begin_at','desc')
                      ->paginate(10)
                      ->toArray();

       if (!empty($lists['data'])){
           foreach ($lists['data'] as &$v) {
               if (strtotime($v['begin_at']) > time()) {
                  $v['live_status'] = '未开始';
               } else {
                   if (strtotime($v['end_at']) < time()) {
                       $v['live_status'] = '已结束';
                   } else {
                       $v['live_status'] = '正在直播';
                   }
               }
               $v['live_time'] =  date('Y.m.d H:i', strtotime($v['begin_at']));
           }
       }
       return success($lists['data']);
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
