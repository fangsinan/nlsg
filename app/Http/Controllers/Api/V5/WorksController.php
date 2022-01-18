<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\CacheTools;
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

class WorksController extends Controller
{


    /**
     * @api {get} /api/v4/works/get_lists_works  专题list      获取集合 模块 数据
     * @apiName get_works_index
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} lists_id  集合id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    code: 200,
    msg: "成功",
    now: 1639637821,
    data: [
    {
    id: 37,
    title: "亲子团购专题",
    subtitle: "精品专题",
    cover: "nlsg/goods/20191122172217903514.png",
    num: 1,
    list_works: [
    {
    id: 76,
    lists_id: 37,
    type: 1,
    works_id: 104,
    works: {
    id: 104,
    user_id: 162021,
    type: 3,
    title: "不要让手机毁了你的孩子！",
    subtitle: "",
    cover_img: "/wechat/works/video/161627/2017071017423885933.jpg",
    original_price: "0.00",
    price: "0.00",
    message: null,
    is_free: 1,
    user: {
    id: 162021,
    nickname: "能量 君",
    headimg: "/wechat/logo.png"
    }
    }
    }
    ]
    }
    ]}
     */
    function getListsWorks(Request $request){
        $lists_id = $request->input("lists_id");
        $page = $request->input("page");
        if(empty($lists_id)){
            return $this->error(0,'参数错误');
        }
        $user_id = $this->user['id'] ?? 0;

        if( !empty($page) && $page > 1 ){
            return $this->success([]);
        }
        $model = new Lists();
        $result = $model->getIndexListWorks([$lists_id], [7,10],$user_id);

        $re = $result[0] ?? [];
        return $this->success($re);

    }



    /**
     * @api {get} /api/v4/works/get_works_index  课程首页
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
     * @api {post} /api/v4/works/neighbor 相邻章节
     * @apiVersion 1.0.0
     * @apiName /api/v4/works/neighbor
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


        $user = $this->user ?? ["id" =>0, "level" =>0, "expire_time" =>0, "new_vip" =>[], ];
        $model = new WorksInfo();
        $data = $model->neighbor($request->input(),$user);
        return $this->getRes($data);
    }


}
