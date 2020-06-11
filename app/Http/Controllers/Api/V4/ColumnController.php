<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\History;
use App\Models\Recommend;
use App\Models\Subscribe;
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
     * @api {post} /api/v4/column/get_column_list 专栏-专栏首页列表
     * @apiName get_column_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} page  （非必填）
     * @apiParam {int} pageSize  (非必填）
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
        $order_str = 'asc';
        if($order){
            $order_str = 'desc';
        }
        $field = ['id', 'name', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic'];
        $list = Column::where("status",'1')->orderBy('updated_at', 'desc')
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

        $field = ['id', 'name', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) )    {
            return $this->error(0,'专栏不存在不能为空');
        }

        $works_data     = [];
        $column_outline = [];
        $historyData    = [];
        //多课程
        if($column['type'] == 1){
            $works_data = Works::select(['id','type','title','cover_img','detail_img','message','is_pay','is_end','is_free','subscribe_num'])
                ->where('column_id', $column_id)->where('status',4)->get();
            foreach ($works_data as $key=>$val){
                $works_data[$key]['is_sub'] = Subscribe::isSubscribe($user_id,$val['id'],2);
            }

        }else if($column['type'] == 2){
            //单课程查询【 多了专栏大纲 】
            //查询专栏对应的关联大纲表 并查询章节
            $outline = ColumnOutline::select('id', 'name')->where('column_id',$column['id'])->orderBy('sort','asc')->get()->toArray();
            ColumnOutline::where('column_id',$column['id'])->count();
            //按照大纲表排序进行数据章节处理
            foreach ($outline as $key=>$val){
                $column_outline[$key]['name'] = $val['name'];
                $works_info   = WorksInfo::where('outline_id',$val['id'])->get()->toArray();
                $works_info_c = WorksInfo::where('outline_id',$val['id'])->count();
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
     * @api {post} /api/v4/column/get_column_detail 专栏-专栏详细信息
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
        $field = ['id', 'name', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) )    {
            return $this->error(0,'专栏不存在不能为空');
        }
        return $this->success([
            'column_info'  =>$column,
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
}
