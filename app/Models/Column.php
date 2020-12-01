<?php


namespace App\Models;


class Column extends Base
{
    protected $table = 'nlsg_column';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = ['name','user_id','subtitle','price','original_price','cover_pic','details_pic','message','status'];

    //状态 1上架  2 下架
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;

    public function getDateFormat()
    {
        return time();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id','id');
    }

    //获取专栏相关信息
    static function getColumnInfo($column_id,$field,$user_id=0){
        $column = Column::where('id',$column_id)->first($field);
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
            $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,6);
        }else{
            //是否购买
            $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,1);
        }
        //是否收藏
        $collection = Collection::where(['type'=>1,'user_id'=>$user_id,'relation_id'=>$column_id])->first();
        $column['is_collection'] = $collection ? 1 : 0;
        return $column;
    }

    /**
     * 首页专栏推荐
     * @param $ids
     * @return bool
     */
    public function getIndexColumn($ids)
    {
        if (!$ids){
            return false;
        }
        $lists= $this->select('id','name', 'column_type', 'title','subtitle', 'message','price', 'cover_pic','info_num as chapter_num','is_free')
            ->whereIn('id', $ids)
            ->where('status',self::STATUS_ONE)
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
        $res = Column::select('id', 'name', 'user_id', 'subtitle', 'original_price', 'price', 'cover_pic')
            ->where('type',$type)
            ->where('status',1)
            ->where(function ($query)use($keywords){
                $query->orWhere('title','LIKE',"%$keywords%");
                $query->orWhere('name','LIKE',"%$keywords%");
                $query->orWhere('subtitle','LIKE',"%$keywords%");
            })->paginate(10)->toArray();
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
