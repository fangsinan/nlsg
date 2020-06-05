<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\History;
use App\Models\Subscribe;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;

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
     * 音频详情列表
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

    //点播时 记录首次历史记录 阅读数自增
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

    //更新学习进度 时长及百分比
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
    /**
     * 收藏课程
     */
    public function worksCollection(Request $request){
        $works_id = $request->input('works_id',0);
        $user_id   = $request->input('user_id',0);
        if( empty($works_id) || empty($user_id) ){
            return $this->error(0,'works_id 或者user_id 不能为空');
        }
        $is_collection = Collection::CollectionData($user_id,$works_id,2);
        return $this->success($is_collection);
    }


}
