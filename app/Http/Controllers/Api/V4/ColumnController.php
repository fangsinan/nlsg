<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\ColumnOutline;
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
     * 专栏首页列表
     */
    public function getColumnList(Request $request){
        //排序
        $order = $request->input('order',1);
        $order_str = 'asc';
        if($order){
            $order_str = 'desc';
        }
        $field = ['id', 'name', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic'];
        $list = Column::where("status",'1')->orderBy('sort',$order_str)->get($field);
        return $this->success($list);
    }


    /**
     * 专栏详情 (单\多课程列表)
     *
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

        $works_data= [];
        $column_outline= [];
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
        }
        $res = [
            'column_info'  =>$column,
            'works_data'   =>$works_data,
            'outline_data' =>$column_outline,
        ];
        return $this->success($res);
    }


    //试读 专栏介绍 [标题...]
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




}
