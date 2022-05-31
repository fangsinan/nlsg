<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\CacheTools;
use App\Models\ChannelWorksList;
use App\Models\Collection;
use App\Models\Column;
use App\Models\GetPriceTools;
use App\Models\History;
use App\Models\Lists;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorksController extends Controller
{


    /**
     * @api {get} /api/v5/works/get_lists_works  专题list      获取集合 模块 数据
     * @apiName get_works_index
     * @apiVersion 5.0.0
     * @apiGroup works
     *
     * @apiParam {int} lists_id  集合id
     */
    function getListsWorks(Request $request){
        $lists_id = $request->input("lists_id");
        $page = $request->input("page");
        $os_type = $request->input("os_type");
        if(empty($lists_id)){
            return $this->error(0,'参数错误');
        }
        $user_id = $this->user['id'] ?? 0;

        if( !empty($page) && $page > 1 ){
            return $this->success([]);
        }
        $model = new Lists();
        $result = $model->getIndexListWorks([$lists_id], [7,10],$user_id,$os_type);

        $re = $result[0] ?? [];
        return $this->success($re);

    }



    /**
     * @api {get} /api/v5/works/get_works_index  课程首页
     * @apiName get_works_index
     * @apiVersion 5.0.0
     * @apiGroup works
     *
     * @apiParam {int} order   1 最多学习  2 最新上架  3最多收藏  4 最多分享
     * @apiParam {int} hide   1 隐藏已购
     * @apiParam {int} category_id 分类id
     * @apiParam {int} user_id
     * @apiParam {int} page  页数
     * @apiParam {int} teacher_id  老师id
     * @apiParam {int} is_free  1免费
     * @apiParam {int} is_audio_book  0全部  1 听书 2课程
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "works": [
    {
    "id": 1,
    "work_id": 16,
    "category_id": 1,
    "created_at": null,
    "updated_at": null,
    "works": {
    "id": 16,
    "user_id": 168934,
    "column_id": 1,
    "type": 1,
    "title": "如何经营幸福婚姻",
    "subtitle": "",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "down_time": null,
    "online_time": null,
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
    "timing_time": null,
    "can_twitter": 0,
    "chapter_num": 0,
    "book_sku": 0,
    "is_audio_book": 1,
    "is_end": 1,
    "roof_placement": 1,
    "is_teaching_aids": 0,
    "is_free": 0,
    "status": 4,
    "works_update_time": null,
    "created_at": null,
    "updated_at": null,
    "is_sub": 1,
    "is_new": 0
    }
    },
    {
    "id": 2,
    "work_id": 18,
    "category_id": 1,
    "created_at": null,
    "updated_at": null,
    "works": {
    "id": 18,
    "user_id": 211172,
    "column_id": 1,
    "type": 2,
    "title": "如何培养高情商孩子",
    "subtitle": "",
    "cover_img": "/wechat/works/video/161910/1639_1525340866.png",
    "detail_img": "/wechat/works/video/1/2017101715260412803.jpg",
    "content": "<p>一个人能否取得成功，智商只起到20%的作用，剩下的80%取决于情商。——许多孩子的学习问题不是智商低，而是缺乏情商培养！</p>",
    "down_time": null,
    "online_time": null,
    "view_num": 3770,
    "message": null,
    "is_pay": 0,
    "original_price": "0.00",
    "price": "0.00",
    "promotion_cost": "0.00",
    "twitter_price": "0.00",
    "subscribe_num": 0,
    "collection_num": 0,
    "timing_online": 0,
    "timing_time": null,
    "can_twitter": 0,
    "chapter_num": 0,
    "book_sku": 0,
    "is_audio_book": 0,
    "is_end": 1,
    "roof_placement": 1,
    "is_teaching_aids": 0,
    "is_free": 0,
    "status": 4,
    "works_update_time": null,
    "created_at": null,
    "updated_at": null,
    "is_sub": 0,
    "is_new": 0
    }
    },
    {
    "id": 3,
    "work_id": 16,
    "category_id": 3,
    "created_at": null,
    "updated_at": null,
    "works": {
    "id": 16,
    "user_id": 168934,
    "column_id": 1,
    "type": 1,
    "title": "如何经营幸福婚姻",
    "subtitle": "",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "detail_img": "/nlsg/works/20191023183946478177.png",
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "down_time": null,
    "online_time": null,
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
    "timing_time": null,
    "can_twitter": 0,
    "chapter_num": 0,
    "book_sku": 0,
    "is_audio_book": 1,
    "is_end": 1,
    "roof_placement": 1,
    "is_teaching_aids": 0,
    "is_free": 0,
    "status": 4,
    "works_update_time": null,
    "created_at": null,
    "updated_at": null,
    "is_sub": 1,
    "is_new": 0
    }
    }
    ],
    "total": 3
    }
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
        $teacher_id = $request->input('teacher_id',0);
        $is_free = $request->input('is_free',0);
        $is_audio_book = $request->input('is_audio_book',0);
        $page = $request->input('page',0);

        $user_id = $this->user['id'] ?? 0;

        $newWorks = [];
        $cache_key_name = 'get_works_index_'.$order.'_'.$hide.'_'.$category_id.'_'.$teacher_id.'_'.$is_free.'_'.$is_audio_book.'_'.$page;
        $worksData = [];
        if($page  < 3){  //只做两页缓存
            $worksData = Cache::get($cache_key_name);
        }

        if(empty($worksData)){

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
                    $order_str = 'created_at';
            }
            $sort = 'desc';

            $where = [];


            if($category_id){
                //查看是否是一级分类   一级分类需展示其二级分类下的数据
                $cate_id_arr = [];
                $cate_data = WorksCategory::find($category_id);
                if( $cate_data['level'] == 1 ){
                    $cate_arr = WorksCategory::select('id')->where(['pid'=>$cate_data['id'],'status'=>1])->get()->toArray();
                    $cate_id_arr = array_column($cate_arr,'id');
                }

                if( empty($cate_id_arr)){
                    $where = ['relation.category_id'=>$category_id];
                }
            }



            $where['works.status'] =4;
            $where['works.type'] =2;  //课程只有音频
            if( $teacher_id )   { $where['works.user_id'] = $teacher_id;}
            if( $is_free )      { $where['works.is_free'] = $is_free;   }

//            if($is_audio_book != 0){
//                //  0全部  1 听书 2课程
//                $is_audio_book_arr = ['1' => 1, '2' => 0,];
//                $where['works.is_audio_book'] = $is_audio_book_arr[$is_audio_book];
//            }
            if( $is_audio_book == 1){  //ios传参有问题
                $where['works.is_audio_book'] = 1;// 先写死  目前只有课程
            }else{
                $where['works.is_audio_book'] = 0;
            }


            $relationObj = new WorksCategoryRelation();
            $worksObj = new Works();
            $worksDb = DB::table($relationObj->getTable(), ' relation')
                ->leftJoin($worksObj->getTable() . ' as works', 'works.id', '=', 'relation.work_id')
                ->select('works.id', 'works.type', 'works.title', 'works.user_id', 'works.cover_img', 'works.price', 'works.original_price', 'works.subtitle',
                    'works.works_update_time','works.detail_img','works.content','relation.id as relation_id','relation.category_id','relation.work_id', 'works.column_id',
                    'works.comment_num','works.chapter_num','works.subscribe_num','works.collection_num','works.is_free');
            if(!empty($cate_id_arr)){
                $worksDb->whereIn('relation.category_id',$cate_id_arr);
            }

            $worksData = $worksDb->where($where)
                ->orderBy('works.'.$order_str,$sort)
                ->groupBy('works.id')->paginate($this->page_per_page)->toArray();


            if($page < 3 ){  //只做第两页缓存
                $expire_num = CacheTools::getExpire('works_index');
                Cache::put($cache_key_name, $worksData, $expire_num);
            }

        }



        $time =Config('web.is_new_time');
        foreach ($worksData['data'] as $key=>&$val){
            $val = (array)$val;


            $is_sub = Subscribe::isSubscribe($user_id,$val['id'],2);
            if($hide == 1){

                //隐藏已购只判断nlsg_subscribe表
                $sub_data = Subscribe::where(['type' => 2, 'user_id' => $user_id,'relation_id'=>$val['id']])
                    ->where('end_time', '>', date('Y-m-d H:i:s'))
                    ->first();
                if($sub_data && $val['is_free'] == 0 ){
                    unset($worksData['data'][$key]);
                    continue;
                }
            }
            $worksData['data'][$key]['is_sub'] = $is_sub ?? 0;

            $is_new = 0;
            if($val['works_update_time'] > $time){
                $is_new = 1;
            }
            $worksData['data'][$key]['is_new'] = $is_new ?? 0;


            //讲师名称
            $user = User::find($val['user_id']);
            $worksData['data'][$key]['username'] = $user['nickname'] ?? '';

            //专栏头衔
//            $column = Column::find($val['column_id']);
            $worksData['data'][$key]['column_title'] = $user['honor'] ?? '';

            $newWorks[$key]['id'] = $val['relation_id'];
            $newWorks[$key]['work_id'] = $val['work_id'];
            $newWorks[$key]['category_id'] = $val['category_id'];
            $newWorks[$key]['works'] = $worksData['data'][$key];

        }
        $newWorks = array_values($newWorks);

        $res = [
            'works' => $newWorks,
            'total' => $worksData['total'],

        ];
        return $this->success($res);

    }


    /**
     * 相邻章节
     * @api {post} /api/v5/works/neighbor 相邻章节
     * @apiVersion 5.0.0
     * @apiName /api/v5/works/neighbor
     * @apiGroup works
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/works/neighbor
     * @apiDescription 相邻章节
     * @apiParam {number} works_id 作品id
     * @apiParam {number} works_info_id 章节id
     * @apiParam {number} type  1 专栏  2作品  6讲座  7训练营
     * @apiParam {number} column_id  专栏/讲座 id
     *
     * @apiSuccess {string[]} list 相邻章节列表
     * @apiSuccess {string} list.previous 上一个
     * @apiSuccess {string} list.current 当前
     * @apiSuccess {string[]} list.next 下一个
     * @apiSuccess {string} list.next.works_info_id 章节id
     * @apiSuccess {string} list.next.works_id 作品id
     * @apiSuccess {string} list.next.info_history 历史记录
     * @apiSuccess {string[]} works 作品信息
     * @apiSuccess {string} works.id 作品id
     * @apiSuccess {string} works.price 价格
     * @apiSuccess {string} works.is_pay  1为精品课
     * @apiSuccess {string} works._is_free  1限免
     * @apiSuccess {string} works.is_sub 1为当前用户订阅了
     */
    public function neighbor(Request $request){


        $user = $this->user ?? ["id" =>0, "level" =>0, "expire_time" =>0, "new_vip" =>['vip_id'=>0], ];
        $model = new WorksInfo();
        $data = $model->neighbor($request->input(),$user);
        return $this->getRes($data);
    }






    /**
     * @api {get} api/v5/works/edit_history_time  更新学习进度 时长及百分比
     * @apiName edit_history_time
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} relation_id  对应id
     * @apiParam {int} relation_type 1专栏   2讲座   3听书    4精品课程    5训练营
     * @apiParam {int} works_info_id 章节id
     * @apiParam {int} time_leng  百分比
     * @apiParam {int} time_number  章节分钟数
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *{
     *  "code": 200,
     *  "msg": "成功",
     *  "data": { }
     *}
     */
    public function editHistoryTime(Request $request){

        DB::table('nlsg_log_info')->insert([
            'url'     => 'infoLog:'.$request->fullUrl(),
            'parameter'    =>  json_encode($request->all()),
            'user_id'    =>  $this->user['id'] ?? 0,
            'created_at' =>date('Y-m-d H:i:s', time())
        ]);


        $user_id    = $this->user['id'] ?? 0;
        $relation_id  = $request->input('relation_id',0);
        $relation_type  = $request->input('relation_type',0);
        $time_leng  = $request->input('time_leng',0);
        $time_number= $request->input('time_number',1);
        $works_info_id = $request->input('works_info_id',0);
        $os_type = $request->input('os_type',0);
        
        $validator = Validator::make($request->all(), [
            'relation_id' => 'required|numeric',
            'relation_type' => 'required|numeric',
            'time_leng' => 'required|numeric|max:100',
            'time_number' => 'required|numeric|min:0',
            'works_info_id' => 'required|numeric',
            // 'info_id' => 'bail:numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first());
        }




        if( empty($user_id) || empty($relation_id) || empty($relation_type)){
            return $this->success();
        }

        $check_his = History::where('relation_id','=',$relation_id)
            ->where('relation_type','=',$relation_type)
            ->where('info_id','=',$works_info_id)
            ->where('user_id','=',$user_id)
            // ->where('is_del','=',0)
            ->first();


        if( empty($check_his)){
            $check_his_num = History::where('relation_id','=',$relation_id)
                    ->where('relation_type','=',$relation_type)
                    ->where('user_id','=',$user_id)
                    ->first();

            //防止 show接口未请求
            $his = History::firstOrCreate([
                'relation_id' =>$relation_id,
                'relation_type'  =>$relation_type,
                'info_id' =>$works_info_id,
                'user_id'   =>$user_id,
                // 'is_del'    =>0,
                // 'os_type'   =>$os_type ?? 0,
            ]);
            if( $his->wasRecentlyCreated){
                // 学习记录数增一
                //4.24号  如果有当前课程id 的记录  不累加历史记录数
                if(empty($check_his_num)){
                    User::where(['id'=>$user_id])->increment('history_num');
                }
            }
            $id = $his->id;
            $end_time = [];
        }else{
            $id = $check_his->id ?? 0;
            $end_time = $check_his->end_time ?? '';
        }


        //客户端传 负数 与 大数字 统一重置为 3000
        if( $time_number <0 || $time_number > 9999 ){
            $time_number = 3000;
        }

        $edit_data = [
            'time_leng'=>$time_leng,
            'time_number'=>$time_number,
            'os_type'   =>$os_type ?? 0,
            'is_del' => 0,
        ];
        if( $time_leng >= 96 ){
            $edit_data['is_end'] = 1;
            //更新end时间
            if( empty($end_time) ){
                $edit_data['end_time'] = date("Y-m-d H:i:s");
            }
            // is_end 之后需要统计是否奖励
            $column = new Column();
            $column->campStudy($relation_id,$user_id,$os_type);
            
        }

        //更新学习进度
        History::where('id',$id)->update($edit_data);
        return $this->success();
    }


    // 配合客户端排查用户黑屏问题
    public function playinfo(Request $request){
        $type = $request->input('type',0);
        if( !empty($type) && $type == 1 ){
            $data = DB::table('nlsg_log_info')->select("url","parameter",'user_id','created_at')->where("url",'like','playinfoLog%')
            ->get()->toArray();
            foreach($data as &$val){
                $val->parameter = json_decode($val->parameter,true);
            }
            return $this->success($data);
        }
        
        DB::table('nlsg_log_info')->insert([
            'url'           =>  'playinfoLog:'.$request->fullUrl(),
            'parameter'     =>  json_encode($request->all()),
            'user_id'       =>  $this->user['id'] ?? 0,
            'created_at'    =>  date('Y-m-d H:i:s', time())
        ]);

        return $this->success();
    }









    /**
     *  {get} api/v5/works/get_works_detail   课程详情
     * @apiName get_works_detail
     * @apiVersion 5.0.0
     * @apiGroup works
     *
     * @apiParam {int} works_id 课程id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} order 排序  asc默认正序 desc
     *
     */
    public function getWorksDetail(Request $request){

        $works_id = $request->input('works_id',0);
        $flag = $request->input('flag','');
        $page = $request->input('page',1);
        $size = $request->input('size',10);
        $user_id   = $this->user['id'] ?? 0;
        $order   = $request->input('order','');
        $activity_tag = $request->input('activity_tag', '');
        //服务器配置了header参数过滤 不能用下划线
        $channel_tag = $request->header('channel-tag','');
        $os_type = $request->input('os_type',0);
        $version = $request->input('version','5.0.0');


        $validator = Validator::make($request->all(), [
            'works_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first());
        }


        if($order == ''){  //默认
            $order = 'asc';
            if($works_id == 566){
                $order = 'desc';
            }
        }

        if( empty($works_id) ){
            return $this->error(0,'works_id 不能为空');
        }
        //是否订阅
        $is_sub = Subscribe::isSubscribe($user_id,$works_id,2);
        if( $is_sub ==1 ){
            $where = [];
        }else{
            $where = ['status'=>4];
        }

        //查询当前课程
        $works_data = Works::select(['id','column_id','user_id' ,'type','title','subtitle', 'original_price', 'price',
            'cover_img','detail_img','message','content','is_pay','is_end','is_free','subscribe_num',
            'collection_num','comment_num','chapter_num','is_free','is_audio_book','view_num'])
            ->where($where)
            ->find($works_id);
        if ($channel_tag === 'cytx') {
            $temp_price = ChannelWorksList::getPrice(2, $works_id);
            if (!empty($temp_price)) {
                $works_data->price = $temp_price;
                $works_data->original_price = $temp_price;
            }
        }
        $works_data->book_works_text = $is_sub == 1 ? '点击文稿查看视频文字版精彩内容' :'解锁大咖讲书即可查看精彩文稿';
        if(empty($works_data)){
            return $this->error(0,'课程不存在或已下架');
        }
        $works_data = $works_data->toArray();
        
        //查询章节
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($works_data['id'],$is_sub,$user_id,1,$order,$this->page_per_page,$page,$size,$works_data,$os_type,$version);
        if ($flag === 'catalog'){           
            $res = [
                'works_info'          => $info,
            ];
            return $this->success($res);
        }
 
        // 身份价格   转换成string保证json_encode 精确度
        $works_data['twitter_price'] = (string)GetPriceTools::Income(1,2,0,2,$works_data['user_id'],$works_id);
        $works_data['black_price']   = (string)GetPriceTools::Income(1,3,0,2,$works_data['user_id'],$works_id);
        $works_data['emperor_price'] = (string)GetPriceTools::Income(1,4,0,2,$works_data['user_id'],$works_id);
        $works_data['service_price'] = (string)GetPriceTools::Income(1,5,0,2,$works_data['user_id'],$works_id);
        $works_data['content']       = $works_data['content'];

        //查询所属专栏
        $field = ['id', 'name', 'type', 'user_id', 'title', 'subtitle', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $field = ['id', 'name', 'type', 'user_id', 'title', 'subtitle', 'message',  'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $column = Column::where('id',$works_data['column_id'])
                    ->first($field);
        if($column){
            $column = $column->toArray();
        }

        $userInfo = User::find($works_data['user_id']);
        $column['title'] = $userInfo['honor'] ?? '';

        $works_data['info_num'] = WorksInfo::where('pid','=',$works_id)->where('status','=',4)->count();

        //作者信息
        //查询课程分类
        $category = WorksCategoryRelation::select('category_id')->with([
            'categoryName'=>function($query) use($works_id){
                $query->select('id','name')->where('status',1);
            }])->where(['work_id'=>$works_id])->first();
        $works_data['category_name'] = $category->CategoryName->name ??'';
        $works_data['user_info'] = User::find($works_data['user_id']);


        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($works_data['id'],4,$user_id);  //讲座
        $works_data['history_count'] = 0;
        if($works_data['info_num'] > 0 ){
            $works_data['history_count'] = round($hisCount/$works_data['info_num']*100);
        }

        $isCollect = Collection::where(['user_id'=>$user_id,'relation_id'=>$works_id,'info_id'=>0, 'type'=>2])->first();

        if($works_data['is_audio_book'] == 0){
            $relation_type = 4;
        }else{
            $relation_type = 3;
        }
        $history_data = History::getHistoryData($works_data['id'],$relation_type,$user_id);
        //免费试听的章节
        $free_trial = WorksInfo::select(['id'])->where(['pid'=>$works_id, 'status' => 4,'free_trial'=>1])->first();

        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'works_info'   => $info,
            'history_data'   => $history_data,
            'is_sub'         => $is_sub ? 1: 0,
            'is_collection'  => $isCollect ? 1 : 0,
            'free_trial_id'  => (string)$free_trial['id'] ?? '',
            'c'=>$channel_tag,
        ];
        return $this->success($res);
    }



    /**
     * {get} /api/v4/works/works_sub_works  免费课程静默订阅操作
     * @apiName works_sub_works
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * apiParam {int} relation_id  订阅id、
     * apiParam {int} sub_type  订阅对象类型  1 专栏  2作品 3直播  4会员 5线下产品  6讲座
     *
     */
    public  function worksSubWorks(Request $request) {

        $relation_id = $request->input('relation_id',0);
        $sub_type = $request->input('sub_type',0);
        $user_id = $this->user['id'] ?? 0;

        if( !is_numeric($relation_id) ){
            return $this->success();
        }
        //校验是否免费
        if($sub_type == 1 || $sub_type ==6){
            $model = new Column();
            $result = $model->getIndexColumn([$relation_id]);
        }
        if($sub_type == 2){
            $model = new Works();
            $result = $model->getIndexWorks([$relation_id]);
        }

        if(empty($result[0]['is_free']) || $result[0]['is_free'] == 0 ){
            return $this->success();
        }

        $starttime = strtotime(date('Y-m-d', time()));
        $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期

        $subscribe = [
            'user_id' => $user_id, //会员id
            'pay_time' => date("Y-m-d H:i:s", $starttime), //支付时间
            'type' => $sub_type,
            'order_id' => 0, //订单id
            'status' => 1,
            'start_time' => date("Y-m-d H:i:s", $starttime),
            'end_time' => date("Y-m-d H:i:s", $endtime),
            'relation_id' => $relation_id,
        ];
        $sub_res = Subscribe::firstOrCreate($subscribe);
        if($sub_res->wasRecentlyCreated){
            if($sub_type == 1 || $sub_type ==6){
                Column::where(['id' => $relation_id])->increment('subscribe_num');
            }else if($sub_type == 2){
                Works::where(['id' => $relation_id])->increment('subscribe_num', 1);
            }
        }


        return $this->success();
    }


}
