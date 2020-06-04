<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psy\Util\Json;


class ColumnController extends Controller
{

    public function index(Request $request)
    {

        //$post = $request->input();
        $name = $request->input('name',0);
        if($name) {
            $field = ['id', 'name', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic'];


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

    }

    /**
     * 专栏首页列表
     */
    public function get_list(Request $request){
        //排序
        $order = $request->input('order',1);
        $order_str = 'asc';
        if($order){
            $order_str = 'desc';
        }
        $list = Column::where("status",'1')->orderBy('sort',$order_str)->get();

//        var_dump($list);


        return Response()->json([
            'code' => '',
            'msg' => '',
            'data'=> $list,
        ]);
    }
}
