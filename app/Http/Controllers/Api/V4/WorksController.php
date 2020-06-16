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
use App\Models\WorksInfoContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorksController extends Controller
{

    public function index()
    {
        return 'hello world';
    }


    /**
     * @api {post} /api/v4/works/get_works_index  课程首页
     * @apiName get_works_index
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} order   1 最多学习  2 最新上架  3最多收藏  4 最多分享
     * @apiParam {int} hide   1 隐藏已购
     * @apiParam {int} category_id 分类id
     * @apiParam {int} user_id
     * @apiParam {int} page  页数
     * @apiParam {int} teacher_id  老师id
     * @apiParam {int} is_free  1免费
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 16,
    "column_id": 1,
    "type": 1,
    "title": "如何经营幸福婚姻",
    "subtitle": "",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "down_time": null,
    "user_id": 168934,
    "online_time": 1571827192,
    "view_num": 1295460,
    "message": null,
    "is_pay": 1,
    "original_price": "29.90",
    "price": "29.90",
    "promotion_cost": "0.00",
    "twitter_price": "0.00",
    "subscribe_num": 287,
    "collection_num": 0,
    "timing_online": 0,
    "timing_time": 0,
    "can_twitter": 0,
    "book_sku": 0,
    "is_audio_book": 0,
    "is_end": 1,
    "roof_placement": 1,
    "is_teaching_aids": 0,
    "is_free": 0,
    "status": 4,
    "created_at": null,
    "updated_at": null
    }
    ]
    }
     */
    public function getWorksIndex(Request $request){
        //order   1 最多学习  2 最新上架  3最多收藏  4 最多分享
        //hide  1 隐藏已购
        //$category_id 分类id
        //is_free 1免费

        $order = $request->input('order',0);
        $hide = $request->input('hide',0);
        $category_id = $request->input('category_id',0);
        $user_id = $request->input('user_id',0);
        $page = $request->input('page',1);
        $teacher_id = $request->input('teacher_id',0);
        $is_free = $request->input('is_free',0);

        switch ($order){
            case 1:
                $order_str = 'subscribe_num';
                break;
            case 2:
                $order_str = 'online_time';
                break;
            case 3:
                $order_str = 'collection_num';
                break;
            case 4:  //分享
                $order_str = 'collection_num';
                break;
            default:
                $order_str = 'updated_at';
        }

        $where = [];
        $newWorks = [];
        if($category_id){
            $where = ['category_id'=>$category_id];
        }

        $works_where['status'] =4;
        if( $teacher_id )   { $works_where['user_id'] = $teacher_id;}
        if( $is_free )      { $works_where['is_free'] = $is_free;   }


        $worksData = WorksCategoryRelation::with([
            'Works' =>function($query) use($order_str,$works_where){
                $query->where($works_where)->select("*")
                    ->orderBy($order_str,'desc')->groupBy('id');
            }])->select()->where($where)
            ->paginate(20);
        $worksData = $worksData->toArray();
        foreach ($worksData['data'] as $key=>$val){
            if($hide == 1){
                $is_sub = Subscribe::isSubscribe($user_id,$val['works']['id'],2);
                if($is_sub == 1){
                    unset($worksData['data'][$key]);
                    continue;
                }
            }
            $newWorks[] = $worksData['data'][$key];

        }
        //$work_data = $worksData->toArray();
        $res = [
            'data' => $newWorks,
            'last_page' => $worksData['last_page'],
            'per_page' => $worksData['per_page'],
            'total' => $worksData['total'],

        ];
        return $this->success($res);

    }



    /**
     * @api {post} /api/v4/works/get_works_category  课程首页分类 名师
     * @apiName get_works_category
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "category": [
    {
    "id": 1,
    "name": "父母关系",
    "count": 2
    },
    {
    "id": 2,
    "name": "亲子关系",
    "count": 0
    }
    ],
    "teacher": [
    {
    "id": 168934,
    "nick_name": "chandler_v4"
    },
    {
    "id": 211172,
    "nick_name": "房某某"
    }
    ]
    }
    }
     */
    public function getWorksCategoryTeacher(Request $request){
//        DB::enableQueryLog();
//        dd(DB::getQueryLog());
        //分类
        $category = WorksCategory::select('id','name')->where([
            'type' => 1, 'status' => 1,
        ])->orderBy('order','desc')->get();

        foreach ($category as $key=>&$val){
            $val['count'] = WorksCategoryRelation::where(['category_id'=>$val->id])->count();
        }

        //精品名师
        $Teacher = Works::select('user_id')->with([
            'UserName'=>function($query){
                $query->select('id','nick_name');
            }])->where('status',4)
            ->orderBy('subscribe_num','desc')->groupBy('user_id')
            ->paginate(6)->toArray();
        $newTeacher = [];
        foreach ($Teacher['data'] as $key=>$val){
            if($val['user_name']){
                $newTeacher[] = $val['user_name'];
            }
        }

        return $this->success(['category'=>$category,'teacher'=>$newTeacher]);
    }


    /**
     * @api {post} api/v4/works/get_works_detail   课程详情
     * @apiName get_works_detail
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} works_id 课程id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} order 排序  asc默认正序 desc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
    */
    public function getWorksDetail(Request $request){
        $works_id = $request->input('works_id',0);
        $user_id   = $request->input('user_id',0);
        $order   = $request->input('order','asc');
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
        $info = $infoObj->getInfo($works_data['id'],$is_sub,$user_id,$order);

        $works_data['info_num'] = count($info);
        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'works_info'   => $info,
        ];
        return $this->success($res);
    }


    /**
     * @api {post} api/v4/works/get_works_content  获取音频文稿
     * @apiName get_works_content
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} workinfo_id 章节id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "id": 1,
    "works_info_id": 16,
    "content": "文稿内容",
    "created_at": null,
    "updated_at": null
    }
    }
     */
    public function getWorksContent(Request $request){
        $info_id = $request->input('info_id',0);
        if( empty($info_id) ){
            return $this->error(0,'info_id 1不能为空');
        }
        $res = WorksInfoContent::where('works_info_id',$info_id)->first();
        return $this->success($res);
    }


    /**
     * @api {post} api/v4/works/show  点播时 记录首次历史记录 阅读数自增
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
     * @api {post} api/v4/works/edit_history_time  更新学习进度 时长及百分比
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
