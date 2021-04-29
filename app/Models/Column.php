<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class Column extends Base
{
    protected $table = 'nlsg_column';

    // 允许批量赋值
    protected  $fillable = ['name','type','user_id','subtitle','price','original_price','cover_pic','details_pic','message','status','online_type', 'online_time','timing_time'];

    //状态 1上架  2 下架
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;



    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id','id');
    }

    //获取专栏相关信息
    static function getColumnInfo($column_id,$field,$user_id=0, $teacher_id=0){
        //兼容老师id (仅限专栏)
        if($column_id){
            $column = Column::where('id',$column_id)->first($field);
        }else{
            $column = Column::where(['type'=>1,'user_id'=>$teacher_id])->first($field);
        }

        if( empty($column) )    {
            return [];
        }
        $category = WorksCategory::select('name')->where(['id'=>$column['category_id'],'type'=>2])->first();
        $column['category_name'] = $category->name ??'';
        //作者信息
        $user = User::find($column['user_id']);
        $column['teacher_data'] = $user;

        //是否关注
        $follow = UserFollow::where(['from_uid'=>$user_id,'to_uid'=>$column['user_id']])->first();
        $column['is_follow'] = $follow ? 1 :0;

        //  在学人数[只存在于讲座]
        $column['user_data'] = [];
        if( $column['type'] == 2 ){
            $sub_user = Subscribe::select('user_id')->where([
                'relation_id'=> $column_id,
                'type'       => 6,
                'is_del'     => 0,
            ])->orderBy('created_at','desc')->paginate(6)->toArray();
            $user_id_arr =array_column($sub_user['data'],'user_id');
            $column['user_data'] = User::select('id','nickname','headimg')->whereIn('id',$user_id_arr)->get()->toArray();
            //$column['user_count'] = Subscribe::where(['relation_id'=> $column_id, 'type' => 6, 'is_del' => 0,])->count();
            //是否购买
//            $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,6);
//            $collection = Collection::where(['type'=>7,'user_id'=>$user_id,'relation_id'=>$column_id])->first();
//        }else if( $column['type'] == 1 ){
//            //是否购买
//            $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,1);
//            $collection = Collection::where(['type'=>1,'user_id'=>$user_id,'relation_id'=>$column_id])->first();
        }

        switch ( $column['type'] ){
            case 1:
                $sub_type = 1;
                $col_type = 1;
                break;
            case 2:
                $sub_type = 6;
                $col_type = 7;
                break;
            case 3:
                $sub_type = 7;
                break;
            default:
                $sub_type = 1;
                $col_type = 1;
                break;

        }
        //是否购买
        $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,$sub_type);

        $collection = Collection::where(['type'=>$col_type,'user_id'=>$user_id,'relation_id'=>$column_id])->first();

        //是否收藏
        $column['is_collection'] = $collection ? 1 : 0;
        return $column;
    }

    /**
     * 首页专栏推荐
     * @param $ids
     * @return bool
     */
    public function getIndexColumn($ids,$is_free=false)
    {
        if (!$ids){
            return false;
        }
        $where = ['status'=>self::STATUS_ONE];
        if($is_free !== false ){
            $where['is_free'] = $is_free;
        }
        $lists= $this->select('id','name', 'column_type', 'title','subtitle', 'message','price', 'cover_pic','info_num as chapter_num','is_free')
            ->whereIn('id', $ids)
            ->where($where)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->toArray();
        foreach ($lists as &$v){
            $v['is_new'] =1;
        }
        return $lists;
    }



    static function search($keywords,$type){
        $columnObj = new Column();
        $userObj = new User();

        $res = DB::table($columnObj->getTable(), 'column')->
        select('column.id', 'column.name', 'user_id', 'subtitle', 'original_price', 'price', 'cover_pic')
            ->leftJoin($userObj->getTable().' as user', 'column.user_id', '=', 'user.id')
            ->where('column.type',$type)
            ->where('column.status',1)
            ->where(function ($query)use($keywords){
                $query->orWhere('column.title','LIKE',"%$keywords%");
                $query->orWhere('column.name','LIKE',"%$keywords%");
                $query->orWhere('column.subtitle','LIKE',"%$keywords%");
                $query->orWhere('user.nickname','LIKE',"%$keywords%");
            })->paginate(100)->toArray();
            //->get();
        return ['res' => $res['data'], 'count'=> $res['total'] ];
//        return ['res' => $res, 'count'=> $res->count() ];

    }


    public  function  getColumnUser()
    {
        $users = User::select('id','nickname')
                ->where('is_author', 1)
                ->orderBy('created_at','desc')
                ->get();
        return $users;
    }
}
