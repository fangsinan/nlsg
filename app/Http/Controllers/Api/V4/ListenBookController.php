<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\History;
use App\Models\Lists;
use App\Models\ListsWork;
use App\Models\Recommend;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use App\Models\Collection;

class ListenBookController extends Controller
{
    /**
     * @api {get} /api/v4/book/get_book_index  听书-听书首页
     * @apiName get_book_index
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function ListenBookIndex(Request $request){
        $recommendObj = new Recommend();

        //主编力荐
        $index_recommend = $recommendObj->getIndexRecommend(9,10 );

        //热门推荐
        $hot_recommend = $recommendObj->getIndexRecommend(9,11 );

        //精选书单
        $book_list = $recommendObj->getIndexRecommend(4,12 );

        //新书速递
        $new_book = $recommendObj->getIndexRecommend(9,13 );

        //热门分类
        $hot_type = WorksCategory::select('id','name')->where(['status'=>1,'type'=>1])->get()->toArray();

        //猜你喜欢
        $like_book = $recommendObj->getIndexRecommend(9,14 );

        return $this->success( [
            'index_recommend'   => $index_recommend ?? [],
            'hot_recommend'     => $hot_recommend ?? [],
            'book_list'         => $book_list ?? [],
            'new_book'          => $new_book ?? [],
            'hot_type'          => $hot_type ?? [],
            'like_book'         => $like_book ?? [],
        ]);
    }



    /**
     * @api {get} /api/v4/book/get_listen_detail  听书-听书详情
     * @apiName get_listen_detail
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} id
     * @apiParam {int} order  asc | desc  默认desc
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function getListenDetail(Request $request){

        $listen_id = $request->input('id',0);
        $flag = $request->input('flag','');
        $page = $request->input('page',1);
        $size = $request->input('size',10);
        $order = $request->input('order','desc');
        $order = $order ?? 'desc';
        $user_id = $this->user['id'] ?? 0;
        $works_data = Works::select([
            'id', 'user_id', 'column_id' ,'type','title','subtitle', 'cover_img','detail_img','message',
            'content','is_pay','is_end', 'is_free','subscribe_num','collection_num','comment_num','chapter_num','original_price','price'])
            ->where('status',4)->find($listen_id);

        if(empty($works_data)){
            return $this->error(0,'该内容不存在或已下架',(object)[]);
        }
        $works_data = $works_data->toArray();

        $is_sub = Subscribe::isSubscribe($user_id,$listen_id,2);
        $works_data['is_sub'] = $is_sub;

        $infoObj = new WorksInfo();
        $works_data['info'] = $infoObj->getInfo($works_data['id'],$is_sub,$user_id,1,$order,50,$page,$size);
        if ($flag === 'catalog'){
            $res = [
                'works_info'          => $works_data['info'] ,
            ];
            return $this->success($res);
        }

        //$works_data['info_num'] = count($works_data['info']);
        $works_data['info_num'] = WorksInfo::where('pid','=',$listen_id)->where('status','=',4)->count();

        //查询课程分类
        $category = WorksCategoryRelation::select('category_id')->with([
            'categoryName'=>function($query) use($listen_id){
                $query->select('id','name')->where('status',1);
            }])->where(['work_id'=>$listen_id])->first();
        $works_data['category_name'] = $category->CategoryName->name ??'';

        //查询总的历史记录进度`
        $hisCount = History::getHistoryCount($works_data['id'],3,$user_id);  //讲座

        $works_data['history_count'] = 0;
        if($works_data['info_num'] > 0){
            $works_data['history_count'] = round($hisCount/$works_data['info_num']*100);
        }

        //作者信息
        $works_data['user_info'] = User::find($works_data['user_id']);

        $field = ['id', 'name', 'type', 'user_id', 'title', 'subtitle', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic', 'is_end', 'subscribe_num'];
        $works_data['column_info'] = Column::where('user_id',$works_data['user_id'])->first($field);

        $works_data['historyData'] = History::getHistoryData($works_data['id'],3,$user_id);

        $isCollect = Collection::where(['user_id'=>$user_id,'relation_id'=>$listen_id,'type'=>6])->first();
        $works_data['is_collection'] = $isCollect ? 1 : 0;
        //免费试听的章节
        $free_trial = WorksInfo::select(['id'])->where(['pid'=>$listen_id, 'status' => 4,'free_trial'=>1])->first();
        $works_data['free_trial_id']  = (string)$free_trial['id'] ?? '';

        return $this->success($works_data);
    }

    /**
     * @api {get} /api/v4/book/get_book_list 听书-精选书单
     * @apiName get_book_list
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function getBookList(Request $request){

        $list = Lists::select(['id', 'title', 'subtitle', 'cover','num as lists_count' ])
            ->where(['status'=>1,'type'=>3])->paginate($this->page_per_page)->toArray();

//        foreach ($list['data'] as $key => &$val){
//            $val['lists_count'] = ListsWork::where(['lists_id'=>$val['id']])->count();
//        }
        return $this->success($list);
    }



    /**
     * @api {get} /api/v4/book/get_book_list_detail 听书-精选书单详情
     * @apiName get_book_list_detail
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} lists_id 书单id
     * @apiParam {int} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "lists_info": {
    "id": 1,
    "title": "世界名著必读，历经岁月经典依旧陪伴成长",
    "subtitle": "强烈推荐",
    "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
    "details_pic": "",
    "num": 5,
    "type": 3,
    "created_at": "2020-06-08T02:00:00.000000Z",
    "updated_at": "2020-06-08T02:00:00.000000Z",
    "status": 1
    },
    "works": [
    {
    "id": 30,
    "user_id": 211172,
    "type": 3,
    "title": "不要羞辱你的孩子 他的心很脆弱",
    "subtitle": "家庭教育",
    "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": "",
    "is_free": 1,
    "user": {
    "id": 211172,
    "nickname": "能量时光",
    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
    },
    "is_sub": 0
    },
    {
    "id": 31,
    "user_id": 168934,
    "type": 3,
    "title": "小孩子做噩梦怎么办？九成父母都没当回事",
    "subtitle": "家庭教育",
    "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": "",
    "is_free": 1,
    "user": {
    "id": 168934,
    "nickname": "chandler",
    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
    },
    "is_sub": 0
    }
    ]
    }
    }
     */
    public function getBookListDetail(Request $request){
        $lists_id = $request->input('lists_id',0);
        $user_id = $this->user['id'] ?? 0;


        $lists_info = Lists::find($lists_id);

        //11.17 增加书单收藏
        $is_collect = Collection::where(['user_id'=>$user_id,'relation_id'=>$lists_id,'type'=>4])->first();
        $lists_info['is_collect'] = $is_collect ? 1 : 0;

        $lists = ListsWork::select('type','works_id')
                ->where(['lists_id'=>$lists_id,'state'=>1])
//                ->orderBy('created_at','desc')
                ->get()
                ->toArray();

        if ($lists){
            foreach ($lists as $k=>$v) {
                if ($v['type']==1){
                    $works = Works::select(['id','user_id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free'])
                          ->with(['user'=>function($query){
                              $query->select('id','nickname', 'headimg');
                          }])
                          ->where('id', $v['works_id'])
                          ->first();
                          //->get()->toArray();
                       $lists[$k]['info_data'] = $works;
                }  elseif ($v['type']==2){
                    $listen = Works::select(['id','user_id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free'])
                          ->with(['user'=>function($query){
                              $query->select('id','nickname', 'headimg');
                          }])
                          ->where('id', $v['works_id'])
                          ->where('is_audio_book', 1)
                        ->first();
                    //->get()->toArray();
                       $lists[$k]['info_data'] = $listen;
                } elseif ($v['type']==3){
                    $column = Column::select(['id','user_id', 'title', 'subtitle', 'cover_pic','original_price','price', 'message','is_free'])
                        ->with(['user'=>function($query){
                            $query->select('id','nickname', 'headimg');
                        }])
                        ->where('id', $v['works_id'])
                        ->where('type', 1)
                        ->first();
                    //->get()->toArray();
                    $lists[$k]['info_data'] = $column;
                } elseif ($v['type']==4 ){
                    $column = Column::select(['id','user_id', 'title', 'subtitle', 'cover_pic','original_price','price', 'message','is_free'])
                         ->with(['user'=>function($query){
                             $query->select('id','nickname', 'headimg');
                         }])
                         ->where('id', $v['works_id'])
                         ->where('type', 2)
                        ->first();
                    //->get()->toArray();
                    $lists[$k]['info_data'] = $column;
                }
            }
        }

        return $this->success(['lists_info'=>$lists_info,'lists'=>$lists]);
    }

    /**
     * @api {get} /api/v4/book/get_new_book_list 听书-新书速递
     * @apiName get_new_book_list
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "current_page": 1,
    "data": [
    {
    "id": 30,
    "type": 3,
    "title": "不要羞辱你的孩子 他的心很脆弱",
    "subtitle": "",
    "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": "",
    "is_free": 0,
    "is_sub": 0
    },
    {
    "id": 31,
    "type": 3,
    "title": "小孩子做噩梦怎么办？九成父母都没当回事",
    "subtitle": "",
    "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": "",
    "is_free": 0,
    "is_sub": 0
    },
    {
    "id": 32,
    "type": 3,
    "title": "时间就像你手中的冰淇淋",
    "subtitle": "",
    "cover_img": "/wechat/works/video/161627/2017061416424169642.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": null,
    "is_free": 0,
    "is_sub": 0
    },
    {
    "id": 33,
    "type": 3,
    "title": "在垃圾桶的手表也是手表",
    "subtitle": "",
    "cover_img": "/wechat/works/video/161627/2017061416503678286.jpg",
    "original_price": "0.00",
    "price": "0.00",
    "message": "",
    "is_free": 0,
    "is_sub": 0
    }
    ],
    "first_page_url": "http://nlsgv4.com/api/v4/book/get_new_book_list?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://nlsgv4.com/api/v4/book/get_new_book_list?page=1",
    "next_page_url": null,
    "path": "http://nlsgv4.com/api/v4/book/get_new_book_list",
    "per_page": 50,
    "prev_page_url": null,
    "to": 4,
    "total": 4
    }
    }
     */
    public function getNewBookList(Request $request){


        $user_id = $this->user['id'] ?? 0;
//        $user_id = $request->input('user_id',0);
//        $works = Works::select(['id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free'])
//            ->where(['status' => 4 , 'is_audio_book' => 1,])
//            ->paginate($this->page_per_page)->toArray();


        $works = Works::select(['id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free','chapter_num', 'user_id'])
            ->with(['user'=>function($query){
            $query->select('id','nickname', 'headimg');
        }])->where(['status' => 4 , 'is_audio_book' => 1,])
            ->paginate($this->page_per_page)->toArray();

        foreach ($works['data'] as $key=>$val){
            //是否购买
            $works['data'][$key]['is_sub'] = Subscribe::isSubscribe($user_id, $val['id'], 2);
        }

        return $this->success($works);
    }


}
