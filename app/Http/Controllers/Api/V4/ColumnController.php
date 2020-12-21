<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnOutline;
use App\Models\GetPriceTools;
use App\Models\History;
use App\Models\Recommend;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\UserFollow;
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
     * @api {get} /api/v4/column/get_column_list 专栏-专栏|讲座首页列表
     * @apiName get_column_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} page
     * @apiParam {int} order 1默认倒序 2正序
     * @apiParam {int} type 1专栏  2讲座
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 1,
    "name": "王琨专栏",   标题
    "type": 1,              //类型 1专栏  2讲座
    "user_id": 211172,
    "message": "",                  //介绍
    "original_price": "0.00",   //原价
    "price": "0.00",            // 金额
    "online_time": 0,
    "works_update_time": 0,             //更新时间
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",  //封面图
    "details_pic": ""               //详情图
    "is_new": 0               //是否最新
    "is_sub": 0               //是否购买【订阅】
    "work_name": 0            //最新章节
    "subscribe_num": 0            //在学人数
    "info_num": 0            //总章节数量「针对讲座」
    },
    {
    "id": 2,
    "name": "张宝萍专栏",
    "type": 1,
    "user_id": 1,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": ""
    },
    {
    "id": 3,
    "name": "王复燕专栏",
    "type": 1,
    "user_id": 211171,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": ""
    }
    ]
    }
     */
    public function getColumnList(Request $request){

        //排序
        $order = $request->input('order',1);
        //type 1 专栏  2讲座
        $type   = $request->input('type',1);
        $order_str = 'asc';
        if($order){
            $order_str = 'desc';
        }
        $field = ['id', 'name','title','subtitle','message', 'column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'subscribe_num', 'info_num','is_free'];
        $list = Column::select($field)->where([
            "status" => 1,
            "type"   => $type,
        ])->orderBy('updated_at', 'desc')
            ->orderBy('sort', $order_str)->paginate($this->page_per_page)->toArray();
        //->get($field);
        //7天前的时间
        $time = Config('web.is_new_time');
        $uid = $this->user['id'] ?? 0;
        foreach ($list['data'] as &$v) {
            $user_info = User::find($v['user_id']);
            $v['is_sub'] = Subscribe::isSubscribe($uid,$v['id']);
            $v['is_new'] = 0;
            if($v['works_update_time'] > $time){
                $v['is_new'] = 1;
            }
            $title = Works::where('column_id',$v['id'])->orderBy('updated_at','desc')->first('title');
            $v['work_name'] = $title->title ?? '';
            $v['nickname'] = $user_info['nickname'] ??'';

        }
        return $this->success($list['data']);
    }


    /**
     * @api {get} /api/v4/column/get_column_works 专栏-专栏详情[课程列表(单\多课程列表)]
     * @apiName get_column_works
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} user_id 用户id  默认0
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column_info": {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",  //详情图
    "is_end": 0,            //是否完结  1完结
    "subscribe_num": 0,     //订阅数
    "teacher_name": "房",      //老师姓名
    "is_sub": 0             //是否订阅
    "is_follow": 0             //是否关注
    },
    "works_data": [         //多课程
    {
    "id": 16,
    "type": 1,
    "title": "如何经营幸福婚姻",            //课程
    "cover_img": "/nlsg/works/20190822150244797760.png",   //课程封面
    "detail_img": "/nlsg/works/20191023183946478177.png",   //课程详情
    "message": null,
    "is_pay": 1,        //是否精品课
    "is_end": 1,        //是否完结
    "is_free": 0,       //是否免费 1是
    "subscribe_num": 287,       关注数
    "is_sub": 0         用户是否购买
    },
    ],
    "outline_data": [],         //单课程  大纲
    "historyData": [],          //历史章节
    }
    }
     */
    public function getColumnWorks(Request $request){
        //排序
        $column_id = $request->input('column_id',0);
        $flag = $request->input('flag','');
        $page = $request->input('page',1);
        $size = $request->input('size',10);
        $order = $request->input('order','asc');
        $user_id   = $this->user['id'] ?? 0 ;

        if( empty($column_id) ){
            return $this->error(0,'column_id 不能为空');
        }

        $field = ['id', 'name', 'column_type', 'title', 'subtitle', 'type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num','collection_num','comment_num','info_num','is_free','category_id','info_num'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) ) {
            return $this->error(0,'该信息不存在');
        }
        $is_sub = Subscribe::isSubscribe($user_id,$column_id,1);

        $works_data     = [];
        $column_outline = [];
        $historyData    = [];
        //多课程
        if($column['column_type'] == 1){
            $works_data = Works::select(['id','type','title','cover_img','detail_img','message','is_pay','is_end',
                'is_free','subscribe_num','chapter_num as info_num','original_price','price'])
                ->where('column_id', $column_id)->where('status',4)->get();
            foreach ($works_data as $key=>$val){
                $works_data[$key]['is_sub'] = Subscribe::isSubscribe($user_id,$val['id'],2);
            }
            $historyData = (object)[];
        }else if($column['column_type'] == 2){
            //单课程查询【 多了专栏大纲 】
            //查询专栏对应的关联大纲表 并查询章节
            $outline = ColumnOutline::select('id', 'name','intro')->where('column_id',$column['id'])
                ->limit($size)->offset(($page - 1) * $size)
                ->orderBy('id',$order)
                ->get()->toArray();
//            ColumnOutline::where('column_id',$column['id'])->count();
            if( $column['is_free'] == 1 ){
                $is_sub = 1;
            }
            $worksInfoObj = new WorksInfo();
            //按照大纲表排序进行数据章节处理
            foreach ($outline as $key=>$val){
                $column_outline[$key]['name'] = $val['name'];
                $column_outline[$key]['intro'] = $val['intro'];
                //处理已购和未购url章节
                $works_info = $worksInfoObj->getInfo($val['id'],$is_sub,$user_id,$type=2,$order);
                $works_info_c = count($works_info);
                $column_outline[$key]['works_info_count'] = $works_info_c;
                $column_outline[$key]['works_info'] = $works_info;
            }

            if ($flag === 'catalog'){
                $res = [
                    'outline_data' => $column_outline,
                ];
                return $this->success($res);
            }

//            //继续学习的章节[时间倒序 第一条为最近学习的章节]
//            $historyData = History::select('relation_id','info_id')->where([
//                'user_id'=>$user_id,
//                'is_del'=>0,
//                'relation_id'=>$column['id'],
//                'relation_type'=>1,
//            ])->orderBy('updated_at','desc')->first();
//            $historyData = $historyData?$historyData->toArray():[];
//            if($historyData){
//                $title = WorksInfo::select('title')->where('id',$historyData['worksinfo_id'])->first();
//                $historyData['title'] = $title->title ?? '';
//            }
            $historyData = History::getHistoryData($column['id'],1,$user_id);

            //查询总的历史记录进度`
            $hisCount = History::getHistoryCount($column_id,1,$user_id);  //讲座
//            $column['history_count'] = round($hisCount/$column['info_num']*100);



            $column['history_count'] = 0;
            if($column['info_num'] > 0){
                $column['history_count'] = round($hisCount/$column['info_num']*100);
            }
            //免费试听的章节
            $free_trial = WorksInfo::select(['id'])->where(['pid'=>$column['id'], 'status' => 4,'free_trial'=>1])->first();
            $column['free_trial_id']  = (string)$free_trial['id'] ?? '';

        }

        $res = [
            'column_info'  => $column,
            'works_data'   => $works_data,
            'outline_data' => $column_outline,
            'historyData'  => $historyData,
        ];
        return $this->success($res);
    }

    //
    /**
     * @api {get} /api/v4/column/get_recommend 相关推荐[专栏|课程]
     * @apiName get_recommend
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} target_id  详情对应的id 专栏id或课程id
     * @apiParam {int} type     类型 1.专栏 2.课堂 3. 讲座 4.书单 5. 百科 6.社区 7.直播 8.好物  9听书
     * @apiParam {int} position 位置 1.首页 2专栏详情  3 课程详情    4精选书单详情  5听书详情   6讲座详情
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    code: 200,
    msg: "成功",
    now: 1606557279,
    data: [
    {
    id: 18,
    name: "邱柏森专栏",
    column_type: 1,
    title: "美国正面管教协会家长/学校双证讲师",
    subtitle: "教练式正面管教 落地有效",
    message: "能量时光，只做家庭教育一件事。大家好，感谢大家关注王琨专栏。今天开始将给大家分享《智慧育儿，听琨来说》系列课程，当然想要听到更为精彩、更为全面的内容，欢迎大家在课程下面留下您精彩的评论。下面我将继续深挖家庭教育优质的课题，持续将优质的家庭教育内容提供给大家。",
    price: "79.50",
    cover_pic: "/wechat/works/video/161627/2017121117542896850.jpg",
    chapter_num: 5,
    is_free: 0,
    is_new: 1,
    recommend_type: 1
    },
    {
    id: 17,
    name: "能量时光",
    column_type: 1,
    title: "让知识变得有温度",
    subtitle: "让知识变得有温度",
    message: "",
    price: "0.00",
    cover_pic: "/wechat/works/video/1/2017082810100337412.jpg",
    chapter_num: 0,
    is_free: 1,
    is_new: 1,
    recommend_type: 1
    },
    {
    id: 573,
    column_id: 21,
    type: 2,
    user_id: 167861,
    title: "女人情商100讲",
    cover_img: "/nlsg/works/20200331175459533892.jpg",
    subtitle: "经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！",
    price: "49.90",
    is_free: 0,
    is_pay: 1,
    works_update_time: null,
    chapter_num: 82,
    sub_num: 424,
    user: {
    id: 167861,
    nickname: "吴岩",
    headimg: "/wechat/works/video/161627/2017121117553852488.jpg"
    },
    is_new: 0,
    is_sub: 0,
    recommend_type: 2
    },
    {
    id: 572,
    column_id: 4,
    type: 2,
    user_id: 161904,
    title: "《琨说：改变你人生的金句名言》",
    cover_img: "/nlsg/works/20200325181759219566.jpg",
    subtitle: "经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！",
    price: "9.90",
    is_free: 0,
    is_pay: 1,
    works_update_time: null,
    chapter_num: 6,
    sub_num: 18,
    user: {
    id: 161904,
    nickname: "王琨",
    headimg: "/wechat/authorpt/wk.png"
    },
    is_new: 0,
    is_sub: 0,
    recommend_type: 2
    },
    {
    id: 570,
    column_id: 23,
    type: 2,
    user_id: 168303,
    title: "青春期叛逆孩子解救营",
    cover_img: "/nlsg/works/20200317132810420958.jpg",
    subtitle: "经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！",
    price: "49.90",
    is_free: 0,
    is_pay: 1,
    works_update_time: null,
    chapter_num: 13,
    sub_num: 45,
    user: {
    id: 168303,
    nickname: "泺仪",
    headimg: "/wechat/authorpt/ly.png"
    },
    is_new: 0,
    is_sub: 0,
    recommend_type: 2
    }
    ]
    }
     */
    public  function getRecommend(Request $request){
        $target_id  = $request->input('target_id',0);
        $type       = $request->input('type',0);
        $position   = $request->input('position',0);
        // position = 4;
        // type = 4
        //相关推荐
        /************    因为要临时更新 不改变数据结构的情况先这么处理   ********************/

        // 查询所属推荐有几种类型
        $list = Recommend::select('relation_id','type')->where('position', $position)
            ->groupBy('type')
            ->get();
        if($list){
            $list = $list->toArray();
        }else{
            return $this->success();
        }
        $recommendLists = [];
        $recommendModel = new Recommend();
        foreach ($list as $key=>$val){
            $recommend = $recommendModel->getIndexRecommend($val['type'], $position);
            array_walk($recommend, function (&$value, $key, $arr) {
                $value = array_merge($value, $arr);
            },['recommend_type'=>$val['type']]);

            $recommendLists = array_merge($recommendLists,$recommend);
        }
        /************    因为要临时更新 不改变数据结构的情况先这么处理   ********************/
        

        if(empty($recommendLists))         return $this->success();

        foreach ($recommendLists as $key=>$val){
            if($target_id && ($val['id'] == $target_id)){
                unset($recommendLists[$key]);
            }
        }
        $recommendLists = array_values($recommendLists);
        return $this->success($recommendLists);

     }



    /**
     * @api {get} /api/v4/column/get_column_detail 讲座-讲座详细信息
     * @apiName get_column_detail
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} column_id  专栏id
     * @apiParam {int} user_id 用户id  默认0
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column_info": {
    "id": 1,
    "name": "王琨专栏",
    "type": 1,
    "user_id": 211172,
    "message": "",
    "original_price": "0.00",
    "price": "0.00",
    "online_time": 0,
    "works_update_time": 0,
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",
    "is_end": 0,
    "subscribe_num": 0,
    "teacher_name": "房爸爸",
    "is_sub": 0
    }
    }
    }
     */

    public function getColumnDetail(Request $request){

        $column_id = $request->input('column_id',0);
        $user_id   = $this->user['id'] ?? 0;
        if( empty($column_id) ){
            return $this->error(0,'column_id 不能为空');
        }
        $field = ['id', 'name','title', 'subtitle','type','column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num','info_num','is_free','category_id'];
        $column = Column::getColumnInfo($column_id,$field,$user_id);
        if( empty($column) ){
            return $this->error(0,'专栏不存在不能为空');
        }


        //免费试听的章节
//        $works = Works::select(['id'])->where(['column_id'=>$column_id, 'status' => 4])->first();

        $free_trial = WorksInfo::select(['id'])->where(['column_id'=>$column_id,'type'=>1, 'status' => 4,'free_trial'=>1])->first();
        $column['free_trial_id']  = (string)$free_trial['id'] ?? '';

        $column['twitter_price'] = (string)GetPriceTools::Income(1,2,0,1,$column_id);
//        $column['black_price']   = GetPriceTools::Income(1,3,0,1,$column_id);
        $column['emperor_price'] = (string)GetPriceTools::Income(1,4,0,1,$column_id);
        $column['service_price'] = (string)GetPriceTools::Income(1,5,0,1,$column_id);

        return $this->success([
            'column_info'  =>$column,
        ]);
    }


    /**
     * @api {get} /api/v4/column/get_lecture_list  讲座目录  针对讲座[讲座与课程一对一]
     * @apiName get_lecture_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} lecture_id  讲座id
     * @apiParam {int} user_id 用户id  默认0
     * @apiParam {int} order asc和 desc  默认desc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     {
    "code": 200,
    "msg": "成功",
    "data": {
    "works_data": {
    "id": 16,
    "title": "如何经营幸福婚姻",  //标题
    "subtitle": "",             //副标题
    "cover_img": "/nlsg/works/20190822150244797760.png",   //封面
    "detail_img": "/nlsg/works/20191023183946478177.png",   //详情图
    "content": "<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>",
    "view_num": 1295460,     //浏览数
    "price": "29.90",
    "subscribe_num": 287,       关注数
    "is_free": 0,
    "is_end": 1,
    "info_num": 2       //现有章节数
    "history_ount": 2%       //总进度
    },
    "info": [
    {
    "id": 2,
    "type": 1,
    "title": "02坚毅品格的重要性",
    "section": "第二章",       //章节数
    "introduce": "第二章",     //章节简介
    "view_num": 246,        //观看数
    "duration": "03:47",
    "free_trial": 0,     //是否可以免费试听
    "href_url": "",
    "time_leng": "10",      //观看 百分比
    "time_number": "5"      //观看 分钟数
    },
    {
    "id": 3,
    "type": 2,
    "title": "03培养坚毅品格的方法",
    "section": "第三章",
    "introduce": "第三章",
    "view_num": 106,
    "duration": "09:09",
    "free_trial": 0,
    "href_url": "",
    "time_leng": "10",
    "time_number": "5"
    }
    ]
    }
    }
     */
    public function getLectureList(Request $request){

        $lecture_id = $request->input('lecture_id',0);
        $order   = $request->input('order','asc');
        $flag = $request->input('flag','');
        $page = $request->input('page',1);
        $size = $request->input('size',10);
        $order = $order ?? 'desc';

        $user_id   = $this->user['id'] ?? 0;
        if(empty($lecture_id)){
            return $this->error(0,'参数有误：lecture_id ');
        }
        //IOS 通过审核后修改  并删除返回值works_data
        $column_data = Column::select(['id','name', 'name as title', 'title','subtitle','cover_pic as cover_img','details_pic as detail_img','message',
            'view_num','price','subscribe_num','is_free','is_end',])
            ->where(['id'=>$lecture_id,'type'=>2,'status'=>1])->first();

//        $works_data = Works::select(['id', 'title','subtitle','cover_img','detail_img','content',
//            'view_num','price','subscribe_num','is_free','is_end',])
//            ->where(['column_id'=>$lecture_id,'type'=>1,'status'=>4])->first();
        $is_sub = Subscribe::isSubscribe($user_id,$lecture_id,6);

        //查询章节、
        $infoObj = new WorksInfo();
        $info = $infoObj->getInfo($lecture_id,$is_sub,$user_id,3,$order,50,$page,$size,$column_data['is_free']);


//        if ($flag === 'catalog'){
//            $res = [
//                'info'          => $info,
//            ];
//            return $this->success($res);
//        }

        $column_data['info_num'] = count($info);
        $column_data['is_sub'] = $is_sub;
        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($lecture_id,2,$user_id);  //讲座
//        $works_data['history_count'] = round($hisCount/$works_data['info_num']*100);


        $column_data['history_count'] = 0;
        if($column_data['info_num'] > 0){
            $column_data['history_count'] = round($hisCount/$column_data['info_num']*100);
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
        if ($flag === 'catalog'){
            $res = [
                'works_data'    => $column_data,
                'lecture_data'    => $column_data,
                'info'          => $info,
            ];
            return $this->success($res);
        }
        $historyData = History::getHistoryData($lecture_id,2,$user_id);

        return $this->success([
            'works_data'    => $column_data,
            'lecture_data'    => $column_data,
            'info'          => $info,
            'historyData'   => $historyData
        ]);
    }





    /**
     * @api {get} /api/v4/column/collection  收藏[专栏、课程、商品]
     * @apiName collection
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} type  type 1专栏  2课程  3商品  4书单 5百科 6听书 7讲座
     * @apiParam {int} target_id  对应id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} info_id 如果是课程 需要传当前章节
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
        "code": 200,
        "msg": "成功",
        "data": {  }
        }
     */
    public function Collection(Request $request){
        $type       = $request->input('type',0);
        $target_id  = $request->input('target_id',0);
        $info_id  = $request->input('info_id',0);
        $user_id   = $this->user['id'] ?? 0;

        if( empty($target_id) || empty($user_id) ){
            return $this->error(0,'column_id 或者 user_id 不能为空');
        }
        //  type 1：专栏  2：课程 3 :商品
        if( !in_array($type,[1,2,3,4,5,6,7]) ){
            return $this->error(0,'type类型错误');
        }
        $is_collection = Collection::CollectionData($user_id,$target_id,$type,$info_id);

        return $this->success($is_collection);
    }

    /**
     * @api {get} api/v4/column/get_lecture_study_list  在学列表
     * @apiName get_lecture_study_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} lecture_id 讲座id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} page 页数
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "data": [
    {
    "id": 3,
    "user_id": 211172,
    "user_info": {
    "id": 211172,
    "level": 0,
    "username": "15650701817",
    "nickname": "能量时光",
    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
    }
    }
    ],
    "last_page": 1,
    "per_page": 20,
    "total": 1
    }
    }
     */
    public function LectureStudyList(Request $request){
        $lecture_id = $request->input('lecture_id',0);
        $user_id    = $this->user['id'] ?? 0;

        $subList = Subscribe::with([
            'UserInfo' => function($query){
                $query->select('id','level','phone','nickname','headimg','expire_time','intro','is_author');
            }])->select('id','user_id')->where([
            'type' => 6,
            'relation_id' => $lecture_id,
        ])->where('end_time','>',time())
            ->paginate($this->page_per_page);
        $subList = $subList->toArray();

        foreach ($subList['data'] as $key => &$val){
            $val['user_info']['level'] = User::getLevel(0, $val['user_info']['level'], $val['user_info']['expire_time']);
            //是否关注
            $follow = UserFollow::where(['from_uid'=>$user_id,'to_uid'=>$val['user_info']['id']])->first();
            $val['user_info']['is_follow'] = $follow ? 1 :0;
            unset($val['user_info']['expire_time']);
        }



        $res = [
            'data' => $subList['data'],
            'last_page' => $subList['last_page'],
            'per_page' => $subList['per_page'],
            'total' => $subList['total'],

        ];
        return $this->success($res);
    }

}
