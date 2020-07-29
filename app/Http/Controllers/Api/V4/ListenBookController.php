<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
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
        $hot_type = WorksCategory::select('id','name')->where('status',1)->get()->toArray();

        //猜你喜欢
        $like_book = $recommendObj->getIndexRecommend(9,14 );

        return $this->success( [
            'index_recommend'   => $index_recommend,
            'hot_recommend'     => $hot_recommend,
            'book_list'         => $book_list,
            'new_book'          => $new_book,
            'hot_type'          => $hot_type,
            'like_book'         => $like_book,
        ]);
    }



    /**
     * @api {get} /api/v4/book/get_listen_detail  听书-听书详情
     * @apiName get_listen_detail
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} id
     * @apiParam {int} user_id  用户id
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
        $user_id = $this->user['id'] ?? 0;
        $works_data = Works::select([
            'id', 'user_id', 'column_id' ,'type','title','subtitle', 'cover_img','detail_img','message',
            'content','is_pay','is_end', 'is_free','subscribe_num','chapter_num','original_price','price'])
            ->where('status',4)->find($listen_id);

        if(empty($works_data)){
            return $this->error(0,'该内容不存在或已下架');
        }
        $works_data = $works_data->toArray();

        //查询课程分类
        $category = WorksCategoryRelation::select('category_id')->with([
            'categoryName'=>function($query) use($listen_id){
                $query->select('id','name')->where('status',1);
            }])->where(['work_id'=>$listen_id])->first();
        $works_data['category_name'] = $category->CategoryName->name ??'';

        $is_sub = Subscribe::isSubscribe($user_id,$listen_id,2);


        $infoObj = new WorksInfo();
        $works_data['info'] = $infoObj->getInfo($works_data['id'],$is_sub,$user_id);
        $works_data['info_num'] = count($works_data['info']);

        //作者信息
        $works_data['user_info'] = User::find($works_data['user_id']);

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

        $list = Lists::select(['id', 'title', 'subtitle', 'cover' ])
            ->where(['status'=>1])->paginate($this->page_per_page)->toArray();

        foreach ($list['data'] as $key => &$val){
            $val['lists_count'] = ListsWork::where(['lists_id'=>$val['id']])->count();
        }
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
        $lists = ListsWork::select('*')->where(['lists_id'=>$lists_id])->paginate($this->page_per_page)->toArray();
        $works_ids = array_column($lists['data'],'works_id');
        //$works_ids = array_column($lists,'works_id');

        $works = Works::select(['id','user_id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free'])
            ->with(['user'=>function($query){
                $query->select('id','nickname', 'headimg');
            }])
            ->where(['status' => 4 , 'is_audio_book' => 1,])
            ->whereIn('id', $works_ids)->get()->toArray();

        foreach ($works as $key=>$val){
            //是否购买
            $works[$key]['is_sub'] = Subscribe::isSubscribe($user_id, $val['id'], 2);
        }

        return $this->success(['lists_info'=>$lists_info,'works'=>$works]);
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