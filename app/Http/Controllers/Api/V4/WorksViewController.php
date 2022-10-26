<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\CacheTools;
use App\Models\ChannelWorksList;
use App\Models\Collection;
use App\Models\Column;
use App\Models\GetPriceTools;
use App\Models\History;
use App\Models\Lists;
use App\Models\ListsWork;
use App\Models\Materials;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use App\Models\WorksInfoContent;
use Doctrine\Inflector\Rules\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WorksViewController extends Controller
{

    public function index()
    {
        return 'hello world';
    }

    private function get_subscribe_num($subscribe_num){
        if($subscribe_num >2000){
            return rand(1000,1500);

        }else{
            return  $subscribe_num;
        }
    }

    /**
     * @api {get} /api/v4/column/get_column_list 专栏-专栏|讲座首页列表
     * @apiName get_column_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} page
     * @apiParam {int} order 1默认倒序 2正序
     * @apiParam {int} type 1专栏  2讲座   3训练营
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 1,
     * "name": "王琨专栏",   标题
     * "type": 1,              //类型 1专栏  2讲座
     * "user_id": 211172,
     * "message": "",                  //介绍
     * "original_price": "0.00",   //原价
     * "price": "0.00",            // 金额
     * "online_time": 0,
     * "works_update_time": 0,             //更新时间
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",  //封面图
     * "details_pic": ""               //详情图
     * "is_new": 0               //是否最新
     * "is_sub": 0               //是否购买【订阅】
     * "work_name": 0            //最新章节
     * "subscribe_num": 0            //在学人数
     * "info_num": 0            //总章节数量「针对讲座」
     * },
     * {
     * "id": 2,
     * "name": "张宝萍专栏",
     * "type": 1,
     * "user_id": 1,
     * "message": "",
     * "original_price": "0.00",
     * "price": "0.00",
     * "online_time": 0,
     * "works_update_time": 0,
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "details_pic": ""
     * },
     * {
     * "id": 3,
     * "name": "王复燕专栏",
     * "type": 1,
     * "user_id": 211171,
     * "message": "",
     * "original_price": "0.00",
     * "price": "0.00",
     * "online_time": 0,
     * "works_update_time": 0,
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "details_pic": ""
     * }
     * ]
     * }
     */
    public function getColumnList(Request $request)
    {

        //排序
        $order = $request->input('order', 1);
        //type 1 专栏  2讲座  3训练营
        $type = $request->input('type', 1);
        $order_str = 'asc';
        if ($order) {
            $order_str = 'desc';
        }
        $is_test=[0];
        if(!empty($this->user['is_test_pay'])){
            $is_test=[0,1];
        }

        $field = ['id', 'name', 'title', 'subtitle', 'message', 'column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time','index_pic', 'cover_pic', 'details_pic', 'subscribe_num', 'info_num', 'is_free', 'is_start','show_info_num'];
        $list = Column::select($field)->where([
            "status" => 1,
            "type" => $type,
            "is_start"=>0,
        ])->whereIn('is_test',$is_test)->orderBy('updated_at', 'desc')
            ->orderBy('sort', $order_str)->paginate($this->page_per_page)->toArray();
        //->get($field);
        //7天前的时间
        $time = Config('web.is_new_time');
        $uid = $this->user['id'] ?? 0;
        $sub_type = 1;  //专栏
        if ($type == 2) {
            $sub_type = 6;   //讲座
        } elseif ($type == 3) {
            $sub_type = 7;   //训练营
        }

        foreach ($list['data'] as &$v) {

            $v['subscribe_num']=$this->get_subscribe_num($v['subscribe_num']);

            $user_info = User::find($v['user_id']);
            $v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], $sub_type);
            $v['is_new'] = 0;
            if ($v['works_update_time'] > $time) {
                $v['is_new'] = 1;
            }
            $title = Works::where('column_id', $v['id'])->orderBy('updated_at', 'desc')->first('title');
            $v['work_name'] = $title->title ?? '';
            $v['nickname'] = $user_info['nickname'] ?? '';
            $v['title'] = $user_info['honor'] ?? '';

        }
        return $this->success($list['data']);
    }


    /**
     * @api {get} /api/v4/column/get_lecture_list  讲座目录  针对讲座和训练营[讲座与课程一对一]
     * @apiName get_lecture_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} lecture_id  讲座id
     * @apiParam {int} user_id 用户id  默认0
     * @apiParam {int} order asc和 desc  默认asc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "works_data": {
     * "id": 16,
     * "title": "如何经营幸福婚姻",  //标题
     * "subtitle": "",             //副标题
     * "cover_img": "/nlsg/works/20190822150244797760.png",   //封面
     * "detail_img": "/nlsg/works/20191023183946478177.png",   //详情图
     * "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
     * "view_num": 1295460,     //浏览数
     * "price": "29.90",
     * "subscribe_num": 287,       关注数
     * "is_free": 0,
     * "is_end": 1,
     * "info_num": 2       //现有章节数
     * "history_ount": 2%       //总进度
     * },
     * "info": [
     * {
     * "id": 2,
     * "type": 1,
     * "title": "02坚毅品格的重要性",
     * "section": "第二章",       //章节数
     * "introduce": "第二章",     //章节简介
     * "view_num": 246,        //观看数
     * "duration": "03:47",
     * "free_trial": 0,     //是否可以免费试听
     * "href_url": "",
     * "time_leng": "10",      //观看 百分比
     * "time_number": "5"      //观看 分钟数
     * },
     * {
     * "id": 3,
     * "type": 2,
     * "title": "03培养坚毅品格的方法",
     * "section": "第三章",
     * "introduce": "第三章",
     * "view_num": 106,
     * "duration": "09:09",
     * "free_trial": 0,
     * "href_url": "",
     * "time_leng": "10",
     * "time_number": "5"
     * }
     * ]
     * }
     * }
     */
    public function getLectureList(Request $request)
    {

        $lecture_id = $request->input('lecture_id', 0);
        $order = $request->input('order', 'asc');
        $flag = $request->input('flag', '');
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $order = $order ?? 'asc';

        $page = intval($page) <= 0 ?1:$page;
        $user_id = $this->user['id'] ?? 0;
        if (empty($lecture_id)) {
            return $this->error(0, '参数有误：lecture_id ');
        }
        //IOS 通过审核后修改  并删除返回值works_data
        $column_data = Column::select(['id', 'name', 'name as title','type' , 'title', 'subtitle','index_pic', 'cover_pic as cover_img', 'details_pic as detail_img', 'message','details_pic','cover_pic',
            'view_num', 'price', 'subscribe_num', 'is_free', 'is_end', 'info_num','show_info_num','info_column_id','status','can_h5'])
//            ->where(['id' => $lecture_id, 'status' => 1])->first();
            ->where(['id' => $lecture_id, ])->first();


        if (empty($column_data)) {
            return $this->error(0, '参数有误：无此信息');
        }


        $column_data['subscribe_num']=$this->get_subscribe_num($column_data['subscribe_num']);


//        $works_data = Works::select(['id', 'title','subtitle','cover_img','detail_img','content',
//            'view_num','price','subscribe_num','is_free','is_end',])
//            ->where(['column_id'=>$lecture_id,'type'=>1,'status'=>4])->first();
        $history_type = 2;
        $getInfo_type = 3;
        if($column_data['type'] == 2 ){
            $type = 6;
        }else if ($column_data['type'] == 3 || $column_data['type'] == 4 ){
            $type = 7;
            $history_type = 5; //训练营
            $getInfo_type = 4; //训练营
            $order = "asc";

        }
        $is_sub = Subscribe::isSubscribe($user_id, $lecture_id, $type);

        //因为需要根据$column_data的type类型校验 sub表  所以需要全部查询后进行上下架状态校验
        //未关注   正常按照上下架 显示数据
        //已关注则不操作
        if($is_sub == 0 && $column_data['type'] == 2 && $column_data['status'] !==1){  //未关注 下架 不显示数据
            return $this->error(0, '产品已下架');
        }

        //1、加字段控制需要查询的章节
        $page_per_page = 50;
        if( $column_data['type'] == 3 ) {   //训练营
            //如果分页到达指定最大数 ，不返回数据
//            $to_page = ceil($column_data['show_info_num']/$size);//应显示的总页数
//
//            if($page == $to_page){
//                //传的页数 = 总页数   则取模  返回数据库指定的剩余数量
//                $size = $column_data['show_info_num']%$size;
//                if($size == 0 ){
//                    $size = 10;  //当前页最大数
//                }
//            }else if($page > $to_page){
//                $page = 100;//传的页数大于总数 不返回数据
//            }
            $size = $column_data['show_info_num'];
            if($page > 1){
                $page = 100;
            }


        }
        $os_type = $request->input('os_type', 0);

        //仅限于训练营  因为多期训练营共用同一章节
        $getInfo_id = $lecture_id;
        if($column_data['info_column_id'] > 0 ){
            $getInfo_id = $column_data['info_column_id'];
        }
        //查询章节、
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($getInfo_id, $is_sub, $user_id, $getInfo_type, $order, $page_per_page, $page, $size, $column_data,$os_type);
        if($column_data['type'] == 3 || $column_data['type'] == 4) {
            //训练营规定展示章节
            $info = array_reverse($info);
        }
        //H5 不返href_url
//        if ($flag === 'catalog'){
//            $res = [
//                'info'          => $info,
//            ];
//            return $this->success($res);
//        }


        $column_data['is_sub'] = $is_sub;
        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($lecture_id, $history_type, $user_id);  //讲座
//        $works_data['history_count'] = round($hisCount/$works_data['info_num']*100);



        $column_data['history_count'] = 0;
        $info_num  = $column_data['type'] == 3 ? $column_data['show_info_num']:$column_data['info_num'];
        if ($info_num > 0) {
            $column_data['history_count'] = round($hisCount / $info_num * 100);
        }

        //继续学习的章节[时间倒序 第一条为最近学习的章节]
//        $historyData = History::select('relation_id','info_id','time_number')->where([
//            'user_id'=>$user_id,
//            'is_del'=>0,
//            'relation_id'=>$works_data['id'],  // 讲座用的对应课程id
//            'relation_type'=>3,
//        ])->orderBy('updated_at','desc')->first();
//        $historyData = $historyData?$historyData->toArray():[];
//        if($historyData){
//            $title = WorksInfo::select('title')->where('id',$historyData['info_id'])->first();
//            $historyData['title'] = $title->title ?? '';
//        }
        if ($flag === 'catalog') {
            $res = [
                'works_data' => $column_data,
                'lecture_data' => $column_data,
                'info' => $info,
            ];
            return $this->success($res);
        }
        $historyData = History::getHistoryData($lecture_id, $history_type, $user_id);

        return $this->success([
            'works_data' => $column_data,
            'lecture_data' => $column_data,
            'info' => $info,
            'historyData' => $historyData
        ]);
    }



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
            $where['works.is_show'] =1;  //是否展示
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


            $val['subscribe_num']=$this->get_subscribe_num($val['subscribe_num']);

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
     * @api {get} /api/v4/works/get_works_category  课程首页分类 名师
     * @apiName get_works_category
     * @apiVersion 1.0.0
     * @apiGroup works
     *get_works_index
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "category": [
    {
    "id": 1,
    "name": "父母关系",
    "count": 2
    },
    {
    "id": 2,
    "name": "亲子关系",
    "count": 0
    }
    ],
    "teacher": [
    {
    "id": 168934,
    "nickname": "chandler_v4"
    },
    {
    "id": 211172,
    "nickname": "房某某"
    }
    ]
    }
    }
     */
    public function getWorksCategoryTeacher(Request $request){

        $cache_key_name = 'index_works_category';
        $res = Cache::get($cache_key_name);
        if ($res) {
            return $this->success($res);
        }


        //分类
        $category = WorksCategory::select('id','name')->where([
            'type' => 1, 'status' => 1,'level'=>1
        ])->orderBy('sort','asc')->get();

        foreach ($category as $key=>&$val){
            $val['count'] = WorksCategoryRelation::where(['category_id'=>$val->id])->count();
        }

        //精品名师
        $Teacher = Works::select(DB::raw('max(subscribe_num) subscribe_num'),'user_id')->with([
            'userName'=>function($query){
                $query->select('id','nickname');
            }])->where('status',4)->where('type',2)->where('is_show',1)
            ->orderBy('subscribe_num','desc')->groupBy('user_id')
            ->paginate(6)->toArray();
        $newTeacher[] = ['id'=>0,'nickname'=>'全部'];
        foreach ($Teacher['data'] as $key=>$val){
            if($val['user_name']){
                $newTeacher[] = $val['user_name'];
            }
        }
        $res = ['category'=>$category,'teacher'=>$newTeacher];

        $expire_num = CacheTools::getExpire('index_works_category');
        Cache::put($cache_key_name, $res, $expire_num);

        return $this->success($res);
    }


    /**
     * @api {get} api/v4/works/get_works_detail   课程详情
     * @apiName get_works_detail
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} works_id 课程id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} order 排序  asc默认正序 desc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
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

        if(empty($works_data)){
            return $this->error(0,'课程不存在或已下架');
        }
        $works_data = $works_data->toArray();
        $works_data['book_works_text'] = $is_sub == 1 ? '点击文稿查看视频文字版精彩内容' :'解锁大咖讲书即可查看精彩文稿';


//        if($works_data['is_free'] == 1){
//            $is_sub = 1; // 免费时全部按关注处理url
//        }
        //查询章节
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($works_data['id'],$is_sub,$user_id,1,$order,$this->page_per_page,$page,$size,$works_data);
        $durations = array_column($info,'duration');
        $book_work_totle_time = 0;
        foreach($durations as $duration_val){
            // 计算大咖讲书总时长
            $times = explode(':',$duration_val);
            $len = count($times);
            // m 245
            if(!empty($times[$len-1])) $book_work_totle_time += intval($times[$len-1]);
            if(!empty($times[$len-2])) $book_work_totle_time += intval($times[$len-2]*60);
            if(!empty($times[$len-3])) $book_work_totle_time += intval($times[$len-3]*3600);
        }
        //52:01
        $works_data['total_time'] = TimeToMinSec($book_work_totle_time);

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


        //$works_data['info_num'] = count($info);
        $works_data['info_num'] = WorksInfo::where('pid','=',$works_id)->where('status','=',4)->count();

        //作者信息
        //查询课程分类
        $category = WorksCategoryRelation::select('category_id')->with([
            'categoryName'=>function($query) use($works_id){
                $query->select('id','name')->where('status',1);
            }])->where(['work_id'=>$works_id])->first();
        $works_data['category_name'] = $category->CategoryName->name ??'';
        $works_data['user_info'] = User::find($works_data['user_id']);
        //是否关注作者
        $follow = UserFollow::where(['from_uid'=>$user_id,'to_uid'=>$works_data['user_id']])->first();
        $works_data['is_follow'] = $follow ? 1 :0;

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

        //大咖讲书
        $is_teacherBook = WorksInfo::IsTeacherBook($works_id);
        $works_data['teacher_book_msg'] = '开通大咖讲书VIP';
        $works_data['is_teacherBook'] = $is_teacherBook;
        if($is_teacherBook){
            $ListsObj = new Lists();
            $listWorkObj = new listsWork();
            $listsdata = DB::table($ListsObj->getTable(), 'l')
               ->leftJoin($listWorkObj->getTable().' as lw', 'l.id', '=', 'lw.lists_id')
               ->select('l.id','l.title','l.price','l.cover')
               ->where('lw.type','=',1)
               ->where('lw.works_id','=',$works_id)
               ->where('l.status','=',1)
               ->where('l.type','=',10)->first();

            // $listsWork = ListsWork::select('id','lists_id')->where(['type'=>1,'works_id'=>$works_id,'lists_id'=>40])->first();
            // $listsdata = Lists::select('id','title','price','cover')->where(['id'=>$listsWork['lists_id'],'type' => 10,'status'=> 1])->first();
            $works_data['teacher_book_price'] = $listsdata->price??'0';
            $works_data['teacher_book_title'] = $listsdata->title??'';
            $works_data['teacher_book_cover'] = $listsdata->cover??'';
            $works_data['teacher_book_lists_id'] = $listsdata->id ??0;
            // 获取第一章节 info_id
            $first_info_id = WorksInfo::select('id')->where(['pid'=>$works_id,'type'=>2,'status'=>4 ])->orderBy('rank','asc')->first();
            $works_data['teacher_book_first_info_id'] = $first_info_id['id'] ?? 0;
        }

        if(isset($works_data['subscribe_num'])){
            $works_data['subscribe_num']=$this->get_subscribe_num($works_data['subscribe_num']);
        }

        if(isset($column['subscribe_num'])){
            $column['subscribe_num']=$this->get_subscribe_num($column['subscribe_num']);
        }

        if(isset($info['subscribe_num'])){
            $info['subscribe_num']=$this->get_subscribe_num($info['subscribe_num']);
        }

        if(isset($history_data->subscribe_num)){
            $history_data->subscribe_num =$this->get_subscribe_num($history_data->subscribe_num );
        }

        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'works_info'   => $info,
            'history_data'   => $history_data,
            'is_sub'         => $is_sub ? 1: 0,
            'is_collection'  => $isCollect ? 1 : 0,
            'free_trial_id'  => (string)($free_trial['id'] ?? ''),
            'c'=>$channel_tag,
        ];
        return $this->success($res);
    }


    /**
     * @api {get} api/v4/works/get_works_content  获取文稿
     * @apiName get_works_content
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} info_id 章节id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "id": 1,
    "works_info_id": 16,
    "content": "文稿内容",
    "created_at": null,
    "updated_at": null
    }
    }
     */
    public function getWorksContent(Request $request){
        // $works_id = $request->input('works_id',0);
        $info_id = $request->input('info_id',0);
        if( empty($info_id) ){
            return $this->error(0,'info_id 1不能为空');
        }

        $infoData = WorksInfo::find($info_id);
        $works_id = $infoData->pid;
        $IsTeacherBook = WorksInfo::IsTeacherBook($works_id);
        $works = Works::select("user_id","detail_img")->find($works_id);
        if($IsTeacherBook == 1){

            // 因为大咖讲书无横图  临时处理 获取老师课程详情的横图
            $img = Works::select("user_id","detail_img")->where([
                "user_id" => $works->user_id,
                "type" => 1,
                "status" => 4,
                "is_show" => 1,
            ])->first();
            $top_img = $img->detail_img??"";
        }else{
            $top_img = $works->detail_img??"";
        }

        $res = WorksInfoContent::where('works_info_id',$info_id)->first();
        if(!empty( $res)){
            $res->top_img = $top_img;
        }else{
            $res = (object)[];
        }
        return $this->success($res);
    }


    /**
     * @api {get} api/v4/works/show  点播时 记录首次历史记录 阅读数自增
     * @apiName show
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} relation_type  1专栏   2讲座   3听书  4精品课程 5训练营
     * @apiParam {int} relation_id   对应id(1专栏对应id但课程  2课程id   3讲座使用对应的课程id )
     * @apiParam {int} works_info_id 章节id
     * @apiParam {int} user_id 用户id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
        {
        "code": 200,
        "msg": "成功",
        "data": { }
        }
     */
    public function show(Request $request){
        $user_id    = $this->user['id'] ?? 0;
        $works_info_id = $request->input('works_info_id',0);
        $relation_type = $request->input('relation_type',0);
        $relation_id = $request->input('relation_id',0);
        $os_type = $request->input('os_type',0);
        if( empty($relation_type) || empty($relation_id)){
            return $this->success();
        }



        //课程和章节自增
        if($relation_type == 5){
            //训练营单独走
            WorksInfo::where(['id'=>$works_info_id])->increment('real_view_num');//实际阅读量
            WorksInfo::where(['id'=>$works_info_id])->increment('view_num');//实际阅读量
            Column::where(['id'=>$relation_id])->increment('real_view_num');
            Column::where(['id'=>$relation_id])->increment('view_num');

        }else{
            Works::edit_view_num($works_info_id,3,1); //虚拟阅读数 3000以下1：50   以上1：5
            WorksInfo::where(['id'=>$works_info_id])->increment('real_view_num');//实际阅读量
            if($relation_type == 1 || $relation_type == 2){
                Works::edit_view_num($relation_id,2,1); //虚拟阅读数 3000以下1：50   以上1：5
                Column::where(['id'=>$relation_id])->increment('real_view_num');
            }elseif($relation_type == 3 || $relation_type == 4){
                Works::edit_view_num($relation_id,1,1); //虚拟阅读数 3000以下1：50   以上1：5
                Works::where(['id'=>$relation_id])->increment('real_view_num');
            }
        }


        if( empty($user_id) ) return $this->success();

        //4月24 查询当前是否有该课程下的历史数据,如果有当前课程id的记录  不累加历史记录数
        $check_his_num = History::select('id')->where('relation_id','=',$relation_id)
                    ->where('relation_type','=',$relation_type)
                    ->where('user_id','=',$user_id)
                    ->first();

        $check_his = History::where('relation_id','=',$relation_id)
            ->where('relation_type','=',$relation_type)
            ->where('user_id','=',$user_id)
            ->where('info_id','=',$works_info_id)
            // ->where('is_del','=',0)
            ->first();


        if(empty($check_his)){
            $his= History::firstOrCreate([
                'relation_id' =>$relation_id,
                'relation_type'  =>$relation_type,
                'info_id' =>$works_info_id,
                'user_id'   =>$user_id,
                // 'is_del'    =>0,
                'os_type'   =>$os_type??0,//  1 安卓 2ios 3微信
            ]);
            //学习记录条数会只按relation_id算   不根据章节走
            if($his->wasRecentlyCreated && empty($check_his_num)){
                // 学习记录数增一
                User::where(['id'=>$user_id])->increment('history_num');
            }
        }



        return $this->success();
    }

    /**
     * @api {get} api/v4/works/edit_history_time  更新学习进度 时长及百分比
     * @apiName edit_history_time
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} relation_id  对应id
     * @apiParam {int} relation_type 1专栏   2讲座   3听书  4精品课程   5训练营
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

//        DB::table('nlsg_log_info')->insert([
//            'url'     => 'infoLog:'.$request->fullUrl(),
//            'parameter'    =>  json_encode($request->all()),
//            'user_id'    =>  $this->user['id'] ?? 0,
//            'created_at' =>date('Y-m-d H:i:s', time())
//        ]);


        $user_id    = $this->user['id'] ?? 0;
        $relation_id  = $request->input('relation_id',0);
        $relation_type  = $request->input('relation_type',0);
        $time_leng  = $request->input('time_leng',0);
        $time_number= $request->input('time_number',0);
        $works_info_id = $request->input('works_info_id',0);
        $os_type = $request->input('os_type',0);

        if( empty($user_id) || empty($relation_id) || empty($relation_type)){
            return $this->success();
        }

        $check_his = History::where('relation_id','=',$relation_id)
            ->where('relation_type','=',$relation_type)
            ->where('info_id','=',$works_info_id)
            ->where('user_id','=',$user_id)
            ->where('is_del','=',0)
            ->first();


        if( empty($check_his)){
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
                User::where(['id'=>$user_id])->increment('history_num');
            }
            $id = $his->id;
        }else{
            $id = $check_his->id ?? 0;
        }

        //客户端传 负数 与 大数字 统一重置为 3000
        if( $time_number <0 || $time_number > 9999 ){
            $time_number = 3000;
        }


        $edit_data = [
            'time_leng'=>$time_leng,
            'time_number'=>$time_number,
            'os_type'   =>$os_type ?? 0,
            'is_del'    =>0,
        ];
        if( $time_leng >= 96 ){
            $edit_data['is_end'] = 1;
        }

        //更新学习进度
        History::where('id',$id)->update($edit_data);
        return $this->success();
    }

    /**
     * @api {post} api/v4/works/subscribe  订阅
     * @apiVersion 4.0.0
     * @apiName  评论列表
     * @apiGroup Works
     *
     * @apiParam {int} id  作品id
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function  subscribe(Request $request)
    {
        $user_id = 1;
        $input = $request->all();
        $list  = Subscribe::where('relation_id', $input['id'])
                    ->where('type', 2)
                    ->where('user_id', $user_id)
                    ->first();
        if ($list){
            return error(1000, '已经订阅了');
        }

        $res = Subscribe::create([
            'user_id'     => $user_id,
            'relation_id' => $input['id'],
            'type'    => 2,
            'status'  => 1
        ]);

        if ($res){
            return success('订阅成功');
        }


    }


    /**
     * @api {get} /api/v4/works/works_category_data  获取分类[app首页和分类列表用]
     * @apiName works_category_data
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} is_index  是否首页
     * @apiParam {int} type  1课程  2 听书
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    code: 200,
    msg: "成功",
    data: [
    {
    id: 1,
    name: "父母关系",
    pid: 0,
    level: 1,
    son: [
    {
    id: 3,
    name: "母子亲密关系",
    pid: 1,
    level: 2,
    son: [ ]
    }
    ]
    },
    {
    id: 2,
    name: "亲子关系",
    pid: 0,
    level: 1,
    son: [ ]
    }
    ]
    }
     */
    public function worksCategory(Request $request){

        $is_index = $request->input('is_index',0);
        $type = $request->input('type',1);
        if($is_index){
            $category = WorksCategory::select('id','name','pid','level')->where([
                'type' => $type, 'status' => 1, 'is_index'=>1,
            ])->orderBy('order','asc')->get()->toArray();
            return $this->success($category);
        }
        $category = WorksCategory::select('id','name','pid','level','sort')->where([
            'type' => $type, 'status' => 1,
        ])->orderBy('sort','asc')->get()->toArray();
        $data = WorksCategory::getCategory($category,0,1);
        return $this->success($data);
    }

    /**
     * @api {get} api/v4/works/materials 作品素材
     * @apiVersion 4.0.0
     * @apiName  materials
     * @apiGroup works
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/works/materials
     *
     * @apiParam {number}  works_id  作品id
     * @apiParam {number}  type 类型 1 文字 2图片
     *
     * @apiSuccess {string}  content  内容
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *
     *         ]
     *     }
     *
     */
    public  function materials(Request $request)
    {
        $works_id = $request->get('works_id');
        $type   = $request->get('type') ??  1;
        if (!$works_id){
            return error('作品id不能为空');
        }
        $lists =  Materials::where('works_id', $works_id)
                ->where('type', $type)
                ->orderBy('created_at','desc')
                ->get()
                ->toArray();
        return success($lists);
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


    /**
     * @api {get} /api/v4/works/works_sub_works  免费课程静默订阅操作
     * @apiName works_sub_works
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} relation_id  订阅id、
     * @apiParam {int} sub_type  订阅对象类型  1 专栏  2作品 3直播  4会员 5线下产品  6讲座
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    code: 200,
    msg: "成功",
    data: []
    }
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


    public function convert()
    {
        WorksInfo::covertVideo();
    }

}
