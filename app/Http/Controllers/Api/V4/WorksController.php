<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\History;
use App\Models\Subscribe;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorksController extends Controller
{

    public function index()
    {
        return 'hello world';
    }

    /**
     * 课程首页
     */
    public function getWorksIndex(Request $request){
    }


    /**
     * 课程分类
     */
    public function getWorksCategory(Request $request){
        //分类
        DB::enableQueryLog();
        $category = WorksCategory::select()->with([
            'CategoryRelation'=>function($query) {
                $query->select('category_id');
            }
        ])->get()->toArray();
        dump($category);
        dd(DB::getQueryLog());
    }


    /**
     * @api {post} /api/v4/column/collection  音频详情列表
     * @apiName collection
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} works_id 课程id
     * @apiParam {int} user_id 用户id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column_info": {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "subtitle": "顶尖导师 经营能量",
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",
    "is_end": 0,
    "subscribe_num": 0
    },
    "works_data": {
    "id": 16,
    "column_id": 1,
    "type": 1,
    "title": "如何经营幸福婚姻",
    "subtitle": "",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "message": null,
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "is_pay": 1,
    "is_end": 1,
    "is_free": 0,
    "subscribe_num": 287,
    "category_name": "父母关系"
    },
    "works_info": [
    {
    "id": 1,
    "type": 2,
    "title": "01何为坚毅",
    "section": "第一章",
    "introduce": "第一章",
    "view_num": 3,
    "duration": "04:35",
    "free_trial": 1,
    "href_url": "http://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3",
    "time_leng": "10",
    "time_number": "5"
    }
    ]
    }
    }
    */
    public function getWorksDetail(Request $request){
        $works_id = $request->input('works_id',0);
        $user_id   = $request->input('user_id',0);
        if( empty($works_id) ){
            return $this->error(0,'works_id 不能为空');
        }
        //查询当前课程
        $works_data = Works::select(['id' ,'column_id' ,'type','title','subtitle', 'cover_img','detail_img','message','content','is_pay','is_end','is_free','subscribe_num'])
            ->where('status',4)->find($works_id);

        if(empty($works_data)){
            return $this->error(0,'课程不存在或已下架');
        }
        $works_data = $works_data->toArray();

        //查询课程分类
        $category = WorksCategoryRelation::select('category_id')->with([
            'CategoryName'=>function($query) use($works_id){
                $query->select('id','name')->where('status',1);
            }])->where(['work_id'=>$works_id])->first();

        $works_data['category_name'] = $category->CategoryName->name;
        //是否订阅
        $is_sub = Subscribe::isSubscribe($user_id,$works_id,2);

        //查询所属专栏
        $field = ['id', 'name', 'type', 'user_id', 'subtitle', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::where('id',$works_data['column_id'])
                    ->first($field);
        if($column){
            $column = $column->toArray();
        }else{
            $column = [];
        }
        //查询章节
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($works_data['id'],$is_sub,$user_id);


        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'works_info'   => $info,
        ];
        return $this->success($res);
    }

    /**
     * @api {post} /v4/works/show  点播时 记录首次历史记录 阅读数自增
     * @apiName show
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} works_id 课程id
     * @apiParam {int} works_info_id 章节id
     * @apiParam {int} user_id 用户id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
        {
        "code": 200,
        "msg": "成功",
        "data": { }
        }
     */
    public function show(Request $request){
        $user_id    = $request->input('user_id',0);
        $column_id  = $request->input('column_id',0);
        $works_id   = $request->input('works_id',0);
        $works_info_id = $request->input('works_info_id',0);
        if( empty($works_id) || empty($works_info_id)){
            return $this->success();
        }
        //课程和章节自增
        WorksInfo::increment('view_num');
        Works::increment('view_num');
        if( empty($user_id) ) return $this->success();

        History::firstOrCreate([
            'column_id' =>$column_id,
            'works_id'  =>$works_id,
            'worksinfo_id' =>$works_info_id,
            'user_id'   =>$user_id,
            'is_del'    =>0,
        ]);
        return $this->success();
    }

    /**
     * @api {post} /v4/works/edit_history_time  更新学习进度 时长及百分比
     * @apiName edit_history_time
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} works_id 课程id
     * @apiParam {int} works_info_id 章节id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} time_leng  百分比
     * @apiParam {int} time_number  章节分钟数
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": { }
    }
     */
    public function editHistoryTime(Request $request){
        $user_id    = $request->input('user_id',0);
        $column_id  = $request->input('column_id',0);
        $works_id   = $request->input('works_id',0);
        $time_leng  = $request->input('time_leng',0);
        $time_number= $request->input('time_number',0);
        $works_info_id = $request->input('works_info_id',0);

        if( empty($user_id) || empty($works_id) || empty($works_info_id)){
            return $this->success();
        }
        //防止 show接口未请求
        $his = History::firstOrCreate([
            'column_id' =>$column_id,
            'works_id'  =>$works_id,
            'worksinfo_id' =>$works_info_id,
            'user_id'   =>$user_id,
            'is_del'    =>0,
        ]);
        //更新学习进度
        History::where('id',$his->id)->update([
            'time_leng'=>$time_leng,
            'time_number'=>$time_number,
            ]);
        return $this->success();
    }


}
