<?php


namespace App\Models;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Column extends Base
{
    protected $table = 'nlsg_column';

    // 允许批量赋值
    protected  $fillable = ['name','type','user_id','subtitle','price','original_price','index_pic', 'cover_pic','details_pic','message','status','online_type', 'online_time','timing_time','subscribe_num','show_info_num','info_num'];

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
        $column_id = $column['id'];
        $category = WorksCategory::select('name')->where(['id'=>$column['category_id'],'type'=>2])->first();
        $column['category_name'] = $category->name ??'';
        //作者信息
        $user = User::select('id','phone','nickname','openid','wxopenid','unionid','birthday','sex','teacher_title','province','city','headimg','headcover','intro','fan_num')
                ->find($column['user_id']);
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
                $col_type = 8;
                break;
            default:
                $sub_type = 1;
                $col_type = 1;
                break;

        }
        //是否购买
        $column['is_sub'] = Subscribe::isSubscribe($user_id,$column_id,$sub_type);

        $collection = Collection::query()
                                ->where([
                                    'type'=>$col_type,'user_id'=>$user_id,'relation_id'=>$column_id,
                                    'app_project_type'=>APP_PROJECT_TYPE,
                                ])
                                ->first();

        //是否收藏
        $column['is_collection'] = $collection ? 1 : 0;
        return $column;
    }

    /**
     * 首页专栏推荐
     * @param $ids
     * @return bool
     */
    public function getIndexColumn($ids,$is_free=false,$check_offline=1)
    {
        if (!$ids){
            return false;
        }
        if ($check_offline === 1){
            $where = ['status'=>self::STATUS_ONE];
        }
        if($is_free !== false ){
            $where['is_free'] = $is_free;
        }
        $lists= $this->select('id','type','name', 'column_type', 'title','subtitle', 'message','price','index_pic', 'cover_pic','details_pic','info_num as chapter_num','is_free','is_start','cover_pic as cover_images','cover_pic as cover_img','classify_column_id','info_column_id')
            ->whereIn('id', $ids)
            ->where($where)
            ->where("app_project_type",APP_PROJECT_TYPE)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->toArray();
        foreach ($lists as &$v){
            $v['is_parent'] = 0;
            if($v['type']  == 4){
                $v['is_parent'] = 1;
            }
            $v['is_new'] =1;
            // 获取第一章节 info_id
            $v['first_info_id'] = Column::getFirstInfo($v['info_column_id'] ?? $v['id']);
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
            ->whereIn('column.is_start',[0,1])
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


    public static  function expire()
    {
        $start = date('Y-m-d H:i:s', time());
        $lists = Subscribe::whereBetween('end_time', [
                                                Carbon::parse($start)->toDateTimeString(),
                                                Carbon::parse('+7 days')->toDateTimeString(),
                                            ])
                           ->where('status', 1)
                           ->pluck('user_id')
                           ->toArray();

        if ($lists){
            $uids  = array_chunk(array_unique($lists), 100, true);
            if ($uids){
                foreach ($uids as $item) {
                     foreach ($item as  $v){
                         JPush::pushNow(strval($v), '您的专栏即将到期');
                     }
                 }
            }
        }

    }


    //获取list
    public function getColumn($param_where=[],$order_str='desc',$page=10){

        if(empty($param_where)){
            return ["data"=>[]];
        }
        $where = ["status" => 1,];
        $field = [
            'id', 'name', 'title', 'subtitle', 'message', 'column_type', 'user_id', 'message', 'original_price',
            'price', 'online_time', 'works_update_time','index_pic', 'cover_pic', 'details_pic', 'subscribe_num',
            'info_num', 'is_free', 'is_start','show_info_num'
        ];
        $query = Column::select($field);
        foreach ($param_where as $key=>$val){
//            if(count($val) !== 3 || empty($val[2])){
            if(count($val) !== 3){
                continue;
            }
            switch ($val[1]){
                case "=":
                    $where[$val[0]] = $val[2];
                    break;
                case "In":
                    $query->whereIn($val[0],$val[2]);
                    break;
                case "NotIn":
                    $query->whereNotIn($val[0],$val[2]);
                    break;
            }

        }
       $list = $query
           ->where('app_project_type','=',APP_PROJECT_TYPE)
           ->where($where)
           ->orderBy('updated_at', 'desc')
            ->orderBy('sort', $order_str)->paginate($page)->toArray();
        return $list;
    }



    // 训练营 历史记录 学习奖励发放  相关
    public function campStudy($camp_id,$user_id,$os_type){

        $column_data = Column::select('id','info_column_id','end_time','show_info_num')->find($camp_id);
        if (empty($column_data)) {
            return ['code'=>0,'msg'=> '参数有误：无此信息'];
        }
        // 训练营 每周开放六节课程 周日不开课
        // 查询训练营目前开放的全部课程 ，没六个章节为一周，查询历史记录表是否完结

        // $type = 7;
        // $history_type = 5; //训练营 历史记录type值
        // $getInfo_type = 4; //训练营 info type值

        // $is_sub = Subscribe::isSubscribe($user_id, $column_data['id'], $type);
        // if($is_sub ==0){
        //     return ['code'=>0,'msg'=> '您当前尚未加入该训练营'];
        // }
        $week_count = (($column_data['show_info_num']-1) / 6);  //去除先导片  开了几周课  其他周为未开始发放奖励状态

        //仅限于训练营  因为多期训练营共用同一章节
        $getInfo_id = $column_data['info_column_id'] > 0 ? $column_data['info_column_id']:$column_data['id'];

        //查询总章节、
        $work_info_ids = WorksInfo::where([
            'column_id'=> $getInfo_id,
            'type'=> 1,
            'status'=> 4,
        ])->where('rank','>',0)->orderBy('rank','asc')->pluck('id')->toArray();;

        // 查看总章节是否学习完成
        $his_data = History::where([
            'relation_id'   => $column_data['id'],
            'user_id'       => $user_id,
            'relation_type' => 5,
            'is_end'        => 1,
        ])->groupBy('info_id')->pluck('info_id')->toArray();

        // 定义周数据

        // dd($info_ids);
        //匹配每周数据是否对应  info_ids 每六节课为一周 去除先导片
        // array_pop($work_info_ids);
        $info_ids = array_chunk($work_info_ids,6);

        $data = [
            'relation_id'   => $camp_id,
            'user_id'       => $user_id,
            'end_time'      => null,
            'os_type'       => $os_type,
        ];
        foreach($info_ids as $key=>$val){

            // $is_end = 0;
            $data['speed_status'] = 0;        // 未开始领取
            //跟历史记录对比 交集和差集   一致说明本周学习完了
            $diff_arr = array_diff($val,array_intersect($val,$his_data));
            if( count($diff_arr) >0  ){
                $data['speed_status'] = 1; //学习未完成
            }else if(!$diff_arr){
                // $is_end = 1;
                $data['end_time'] = date("Y-m-d H:i:s");
                $data['speed_status'] = 2; //学习完成
            }
            // $data['is_end'] = $is_end;
            $data['week_num'] = $key+1;

            if( $data['week_num'] > $week_count){
                $data['speed_status'] = 0;  //学习奖励不开放领取
            }

            $Reward = ColumnWeekReward::where([
                'relation_id'   =>$camp_id,
                'user_id'       =>$user_id,
                'week_num'      => $key+1,
            ])->first();
            if(!empty($Reward)){
                ColumnWeekReward::where(['id'=>$Reward->id])->update($data);
            }else{
                ColumnWeekReward::create($data);
            }
        }
    }


    public static function getCampBanner($column_id,$user,$params){

        $column_banner = (object)[];
        if( !empty($params['version']) && version_compare($params['version'], "5.0.0", '>=') ){  //新版出现
            // 需要针对每个训练营进行一对一管、

            $column_banner = DB::table("nlsg_camp_banner")->select("id","column_id","jump_type","obj_id","h5_url", "image", "text")
                            ->where(['column_id'=>$column_id,'is_show'=>1])->first();
            // 如果是360类型  需要单独校验 开通\续费
            if(!empty($column_banner) && $column_banner->jump_type == 7){
                $is_vip = 0;
                if(!empty($user)){
                    $is_vip = $user['new_vip']['vip_id'] ?1:0;
                }
                $column_banner = DB::table("nlsg_camp_banner")->select("id","column_id","jump_type","obj_id","h5_url", "image", "text")
                ->where(['column_id'=>$column_id,'is_show'=>1,'is_vip'=>$is_vip])->first();
            }
            if(empty($column_banner)){
                $column_banner =  (object)[];
            }
        }


        return $column_banner;
    }

    // 获取训练营第一章节id
    static function getFirstInfo($get_id){

        $first_info_id = WorksInfo::select('id')->where(['column_id'=>$get_id,'type'=>1,'status'=>4,'app_project_type'=>APP_PROJECT_TYPE ])->orderBy('rank','asc')->first();
        return  $first_info_id['id'] ?? 0;
    }


    static function getColumnNewStartTime($id) {
        $online_time = Column::where(["type"=>3,"column_type"=>1,"classify_column_id"=>$id,"is_start"=>0])->OrderBy("online_time","asc")->value("online_time");
        if(empty($online_time)) {
            return "";
        }else{
            return date('Y-m-d',strtotime($online_time));
        }

    }

    public function categoryRelation()
    {
        //一对多
        return $this->hasMany('App\Models\WorksCategoryRelation', 'work_id', 'id')
            ->select(['id', 'work_id', 'category_id']);
    }



    public static function ColumnBind($live_id=0,$user_id=0){
        $remark = "";
        if(!empty($live_id)){
            $remark = "-直播间：".$live_id;
        }
        // 购买2980 查询是否有绑定  如果存在则延长为永久  不存在则不处理
        $AdminInfo = User::find($user_id);
        // $twitter_data = User::find($twitter_id);
        $check_bind = VipUserBind::getBindParent($AdminInfo['phone']);
        //没有绑定记录,则绑定
        // if (($check_bind > 0) && strlen($twitter_data['phone']) === 11 && strlen($AdminInfo['phone']) === 11) {
        if (($check_bind > 0) &&  strlen($AdminInfo['phone']) === 11) {
            DB::table('nlsg_vip_user_bind')->where([
                'son' => $AdminInfo['phone'],
                'status' => 1,
            ])->update([
                'life' => 1,
                'remark' => "购买2980，修改为永久.".$remark
            ]);
        }
    }


}
