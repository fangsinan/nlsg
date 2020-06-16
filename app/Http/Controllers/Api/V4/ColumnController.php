<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\History;
use App\Models\Recommend;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;

class ColumnController extends Controller
{

    public function index(Request $request)
    {
        return 'hello world';

    }


    /**
     * @api {post} /api/v4/column/get_column_list 专栏-专栏|讲座首页列表
     * @apiName get_column_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} page
     * @apiParam {int} order 1默认倒序 2正序
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": ""
    },
    {
    "id": 2,
    "name": "张宝萍专栏",
    "type": 1,
    "user_id": 1,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": ""
    },
    {
    "id": 3,
    "name": "王复燕专栏",
    "type": 1,
    "user_id": 211171,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": ""
    }
    ]
    }
     */
    public function getColumnList(Request $request){
        //排序
        $order = $request->input('order',1);
        //type 1 专栏  2讲座
        $type   = $request->input('type',1);
        $order_str = 'asc';
        if($order){
            $order_str = 'desc';
        }
        $field = ['id', 'name', 'column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic'];
        $list = Column::where([
            "status" => 1,
            "type"   => $type,
        ])->orderBy('updated_at', 'desc')
                    ->orderBy('sort', $order_str)->get($field);
        return $this->success($list);
    }


    /**
     * @api {post} /api/v4/column/get_column_works 专栏-专栏详情[课程列表(单\多课程列表)]
     * @apiName get_column_works
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} user_id 用户id  默认0
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column_info": {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",
    "is_end": 0,
    "subscribe_num": 0,
    "teacher_name": "房爸爸",
    "is_sub": 0
    },
    "works_data": [
    {
    "id": 16,
    "type": 1,
    "title": "如何经营幸福婚姻",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "message": null,
    "is_pay": 1,
    "is_end": 1,
    "is_free": 0,
    "subscribe_num": 287,
    "is_sub": 0
    },
    ],
    "outline_data": [],
    "historyData": [],
    "recommendLists": [
    {
    "id": 2,
    "name": "张宝萍专栏",
    "title": "国家十百千万工程心灵导师",
    "subtitle": "心灵导师 直击人心",
    "message": "",
    "price": "0.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
    }
    ]
    }
    }
     */
    public function getColumnWorks(Request $request){
        //排序
        $column_id = $request->input('column_id',0);
        $user_id   = $request->input('user_id',0);

        if( empty($column_id) ){
            return $this->error(0,'column_id 不能为空');
        }

        $field = ['id', 'name', 'column_type', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) )    {
            return $this->error(0,'专栏不存在不能为空');
        }
        $is_sub = Subscribe::isSubscribe($user_id,$column_id,1);

        $works_data     = [];
        $column_outline = [];
        $historyData    = [];
        //多课程
        if($column['column_type'] == 1){
            $works_data = Works::select(['id','type','title','cover_img','detail_img','message','is_pay','is_end','is_free','subscribe_num'])
                ->where('column_id', $column_id)->where('status',4)->get();
            foreach ($works_data as $key=>$val){
                $works_data[$key]['is_sub'] = Subscribe::isSubscribe($user_id,$val['id'],2);
            }

        }else if($column['column_type'] == 2){
            //单课程查询【 多了专栏大纲 】
            //查询专栏对应的关联大纲表 并查询章节
            $outline = ColumnOutline::select('id', 'name')->where('column_id',$column['id'])->orderBy('sort','asc')->get()->toArray();
//            ColumnOutline::where('column_id',$column['id'])->count();
            $worksInfoObj = new WorksInfo();
            //按照大纲表排序进行数据章节处理
            foreach ($outline as $key=>$val){
                $column_outline[$key]['name'] = $val['name'];
                //处理已购和未购url章节
                $works_info = $worksInfoObj->getInfo($val['id'],$is_sub,$user_id,$type=2);
                $works_info_c = count($works_info);
                $column_outline[$key]['works_info_count'] = $works_info_c;
                $column_outline[$key]['works_info'] = $works_info;
            }

            //继续学习的章节[时间倒序 第一条为最近学习的章节]
            $historyData = History::select('works_id','worksinfo_id')->where([
                'user_id'=>$user_id,
                'is_del'=>0,
                'column_id'=>$column['id'],
            ])->orderBy('updated_at','desc')->first();
            $historyData = $historyData?$historyData->toArray():[];
            if($historyData){
                $title = WorksInfo::select('title')->where('id',$historyData['worksinfo_id'])->first();
                $historyData['title'] = $title->title ?? '';
            }

        }

        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'outline_data' => $column_outline,
            'historyData'  => $historyData,
        ];
        return $this->success($res);
    }

    //
    /**
     * @api {post} /api/v4/column/get_recommend 相关推荐[专栏|课程]
     * @apiName get_recommend
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} target_id  详情对应的id 专栏id或课程id
     * @apiParam {int} type 1.专栏 2.课堂 3. 讲座 4.听书
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 1,
    "name": "王琨专栏",
    "title": "顶尖导师 经营能量",
    "subtitle": "顶尖导师 经营能量",
    "message": "",
    "price": "99.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
    },
    {
    "id": 2,
    "name": "张宝萍专栏",
    "title": "国家十百千万工程心灵导师",
    "subtitle": "心灵导师 直击人心",
    "message": "",
    "price": "0.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
    }
    ]
    }
     */
    public  function getRecommend(Request $request){
        $target_id  = $request->input('target_id',0);
        $type       = $request->input('type',0);
        $position   = $request->input('position',0);

        //相关推荐
        $recommendModel = new Recommend();
        $recommendLists = $recommendModel->getIndexRecommend($type, $position);
        if($recommendLists == false)         return $this->success();

        foreach ($recommendLists as $key=>$val){
            if($target_id && ($val['id'] == $target_id)){
                unset($recommendLists[$key]);
            }
        }
        $recommendLists = array_values($recommendLists);
        return $this->success($recommendLists);

     }



    /**
     * @api {post} /api/v4/column/get_column_detail 专栏-专栏|讲座详细信息
     * @apiName get_column_detail
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} user_id 用户id  默认0
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column_info": {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",
    "is_end": 0,
    "subscribe_num": 0,
    "teacher_name": "房爸爸",
    "is_sub": 0
    }
    }
    }
     */

    public function getColumnDetail(Request $request){

        $column_id = $request->input('column_id',0);
        $user_id   = $request->input('user_id',0);
        if( empty($column_id) ){
            return $this->error(0,'column_id 不能为空');
        }
        $field = ['id', 'name', 'type','column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) )    {
            return $this->error(0,'专栏不存在不能为空');
        }
        return $this->success([
            'column_info'  =>$column,
        ]);
    }


    /**
     * @api {post} /api/v4/column/get_lecture_list  讲座目录  针对讲座[讲座与课程一对一]
     * @apiName get_column_detail
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} lecture_id  讲座id
     * @apiParam {int} user_id 用户id  默认0
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     {
    "code": 200,
    "msg": "成功",
    "data": {
    "works_data": {
    "id": 16,
    "title": "如何经营幸福婚姻",
    "subtitle": "",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "view_num": 1295460,
    "price": "29.90",
    "subscribe_num": 287,
    "is_free": 0,
    "is_end": 1,
    "info_num": 3
    },
    "info": [
    {
    "id": 1,
    "type": 1,
    "title": "01何为坚毅",
    "section": "第一章",
    "introduce": "第一章",
    "view_num": 3,
    "duration": "04:35",
    "free_trial": 1,
    "href_url": "http://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3",
    "time_leng": "10",
    "time_number": "5"
    },
    {
    "id": 2,
    "type": 1,
    "title": "02坚毅品格的重要性",
    "section": "第二章",
    "introduce": "第二章",
    "view_num": 246,
    "duration": "03:47",
    "free_trial": 0,
    "href_url": "",
    "time_leng": "10",
    "time_number": "5"
    },
    {
    "id": 3,
    "type": 2,
    "title": "03培养坚毅品格的方法",
    "section": "第三章",
    "introduce": "第三章",
    "view_num": 106,
    "duration": "09:09",
    "free_trial": 0,
    "href_url": "",
    "time_leng": "10",
    "time_number": "5"
    }
    ]
    }
    }
     */
    public function getLectureList(Request $request){

        $lecture_id = $request->input('lecture_id',0);
        $user_id   = $request->input('user_id',0);
        if(empty($lecture_id)){
            return $this->error(0,'参数有误：lecture_id ');
        }
        $works_data = Works::select(['id', 'title','subtitle','cover_img','detail_img','content','view_num','price','subscribe_num','is_free','is_end',])
            ->where(['column_id'=>$lecture_id,'type'=>1,'status'=>4])->first();
        $is_sub = Subscribe::isSubscribe($user_id,$lecture_id,1);
        //查询章节、
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($works_data['id'],$is_sub,$user_id);
        $works_data['info_num'] = count($info);


        //继续学习的章节[时间倒序 第一条为最近学习的章节]
        $historyData = History::select('works_id','worksinfo_id')->where([
            'user_id'=>$user_id,
            'is_del'=>0,
            'column_id'=>$lecture_id,
        ])->orderBy('updated_at','desc')->first();
        $historyData = $historyData?$historyData->toArray():[];
        if($historyData){
            $title = WorksInfo::select('title')->where('id',$historyData['worksinfo_id'])->first();
            $historyData['title'] = $title->title ?? '';
        }

        return $this->success([
            'works_data'    => $works_data,
            'info'          => $info,
            'historyData'   => $historyData
        ]);
    }





    /**
     * @api {post} /api/v4/column/collection  收藏[专栏、课程、商品]
     * @apiName collection
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} type  type 1：专栏  2：课程 3 :商品
     * @apiParam {int} target_id  对应id
     * @apiParam {int} user_id 用户id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
        "code": 200,
        "msg": "成功",
        "data": {  }
        }
     */
    public function Collection(Request $request){
        $type       = $request->input('type',0);
        $target_id  = $request->input('target_id',0);
        $user_id    = $request->input('user_id',0);

        if( empty($column_id) || empty($user_id) ){
            return $this->error(0,'column_id 或者 user_id 不能为空');
        }
        //  type 1：专栏  2：课程 3 :商品
        if( !in_array($type,[1,2,3]) ){
            return $this->error(0,'type类型错误');
        }
        $is_collection = Collection::CollectionData($user_id,$target_id,$type);

        return $this->success($is_collection);
    }

    /**
     * @api {post} api/v4/column/get_lecture_study_list  在学列表
     * @apiName get_lecture_study_list
     * @apiVersion 1.0.0
     * @apiGroup column
     *
     * @apiParam {int} lecture_id 讲座id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} page 页数
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "data": [
    {
    "id": 3,
    "user_id": 211172,
    "user_info": {
    "id": 211172,
    "level": 0,
    "username": "15650701817",
    "nick_name": "能量时光",
    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
    }
    }
    ],
    "last_page": 1,
    "per_page": 20,
    "total": 1
    }
    }
     */

    public function LectureStudyList(Request $request){
        $lecture_id = $request->input('lecture_id',0);
        $user_id    = $request->input('user_id',0);

        $subList = Subscribe::with([
            'UserInfo' => function($query){
                $query->select('id','level','username','nick_name','headimg','expire_time');
            }])->select('id','user_id')->where([
            'type' => 6,
            'relation_id' => $lecture_id,
        ])->where('end_time','>',time())
            ->paginate($this->page_per_page);
        $subList = $subList->toArray();

        foreach ($subList['data'] as $key => &$val){
            $val['user_info']['level'] = User::getLevel(0, $val['user_info']['level'], $val['user_info']['expire_time']);
            unset($val['user_info']['expire_time']);
        }



        $res = [
            'data' => $subList['data'],
            'last_page' => $subList['last_page'],
            'per_page' => $subList['per_page'],
            'total' => $subList['total'],

        ];
        return $this->success($res);
    }

}
