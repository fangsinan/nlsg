<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Lists;
use App\Models\ListsWork;
use App\Models\Subscribe;
use App\Models\Works;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListenBookController extends Controller
{
    //  听书首页
    public function ListenBookIndex(){
        //主编力荐

        //热门推荐

        //精选书单

        //新书速递

        //热门分类

        //猜你喜欢

    }


    /**
     * @api {post} /api/v4/book/get_book_list 听书-精选书单
     * @apiName get_book_list
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     */
    public function getBookList(Request $request){

        $list = Lists::select(['id', 'title', 'subtitle', 'cover', ])->where([
            'status'=>1
            ])->paginate($this->page_per_page)->toArray();

        foreach ($list['data'] as $key => &$val){
            $val['lists_count'] = ListsWork::where(['lists_id'=>$val['id']])->count();
        }
        return $this->success($list);
    }



    /**
     * @api {post} /api/v4/book/get_book_list_detail 听书-精选书单详情
     * @apiName get_book_list_detail
     * @apiVersion 1.0.0
     * @apiGroup book
     *
     * @apiParam {int} lists_id 书单id
     * @apiParam {int} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     */
    public function getBookListDetail(Request $request){
        $lists_id = $request->input('lists_id',0);
        $user_id = $request->input('user_id',0);

        $lists = ListsWork::select('*')->where(['lists_id'=>$lists_id])->paginate($this->page_per_page)->toArray();
        $works_ids = array_column($lists['data'],'works_id');
        //$works_ids = array_column($lists,'works_id');

        $works = Works::select(['id','type', 'title', 'subtitle', 'cover_img','original_price','price', 'message','is_free'])
            ->where(['status' => 4 , 'is_audio_book' => 1,])
            ->whereIn('id', $works_ids)->get()->toArray();

        foreach ($works as $key=>$val){
            //是否购买
            $works[$key]['is_sub'] = Subscribe::isSubscribe($user_id, $val['id'], 2);
        }

        return $this->success($works);
    }
}