<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksInfo;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psy\Util\Json;


class ColumnController extends Controller
{

    public function index(Request $request)
    {

//            $model = Column::whereHas('user', function ($query) use ($name) {
//                $query->where('username', $name);
//            })->get('nlsg_user.name','id');


//            $model = Column::whereHas('user', function ($query) use ($name) {
//                $query->where('id', 211172);
//            })->where('id', 3)->with(
////                'user:id','user:username'
//                ['user'=>function($query){
//                    $query->select('id','username');
//                }]
//            )->get();


//            dd($model);die;
            //$email = Column::where('status',1)->get($field);
            //$email = Column::first($field);
//            all();
//            find();
//            findOrFail(2)
//            get()
//            first()
//            chunk();

//            echo "<pre>";
//            Column::chunk(1, function ($c){
//                var_dump($c);
//            });
//            $c = Column::min('id');
//            dd($c);
//            $cObj = new Column();
//            $cObj->name = '专栏1';
//            $cObj->user_id = 211172;
//            $c = $cObj->save();

//            $c = Column::create([
//                    'name'=>'专栏2',
//                    'user_id'=>211172,]
//            );
//            $c = Column::firstOrCreate([
//                    'name'=>'专栏2',
//                    'user_id'=>211172,
//                ]);
//            $c = Column::where('id',7)->update([
//                'name'=>'专栏2',
//                'status'=>2,
//            ]);

            //通过主键删除 str or arr
            //Column::destroy(4,10);

//            $c = Column::where('id',7)->delete();
//            dd($c);


//            $email = Column::where('status',1)->orderBy()->get($field);
//            dd($email);

//            $email = Column::where('status',1)->paginate(1);

//var_dump($email);die;
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



    public function getColumnDetail(Request $request){
        //排序
        $column_id = $request->input('column_id',0);
        $user_id   = $request->input('user_id',0);
        if( empty($column_id) ){
            return $this->error(0,'column_id 不能为空');
        }

        $field = ['id', 'name', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic','subscribe_num'];
        $column = Column::where('id',$column_id)
                    ->first($field)->toArray();
        if( empty($column) )    {
            return $this->error(0,'专栏不存在不能为空');
        }
        //是否关注
        $column['is_sub'] = Column::isSubscribe($user_id,$column['user_id'],1);


        $works_data= [];
        $column_outline= [];
        //多课程
        if($column['type'] == 1){
            $works_data = Works::select(['id','type','title','cover_img','detail_img','message','is_pay'])->where('status',4)
                ->where('status',4)->get();

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



}
