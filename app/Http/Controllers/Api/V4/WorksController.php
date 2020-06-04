<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\ColumnOutline;
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
     * 音频详情列表
     */
    public function getWorksDetail(Request $request){
        //排序
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
        $is_sub = Column::isSubscribe($user_id,$works_id,2);

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
        $info = $infoObj->getInfo($works_data['id'],$is_sub);


        $res = [
            'column_info'  =>$column,
            'works_data'   =>$works_data,
        ];
        return $this->success($res);
    }



}
