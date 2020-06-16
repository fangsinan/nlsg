<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListenBook extends Controller
{

    /**
     * @api {post} /api/v4/column/get_book_list 听书-精选书单
     * @apiName get_column_list
     * @apiVersion 1.0.0
     * @apiGroup Column
     *
     * @apiParam {int} page  （非必填）
     * @apiParam {int} pageSize  (非必填）
     * @apiParam {int} order 1默认倒序 2正序
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
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
    "details_pic": ""
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
        $field = ['id', 'name', 'column_type', 'user_id', 'message', 'original_price', 'price', 'online_time', 'works_update_time', 'cover_pic', 'details_pic'];
        $list = Column::where([
            "status" => 1,
            "type"   => $type,
        ])->orderBy('updated_at', 'desc')
            ->orderBy('sort', $order_str)->get($field);
        return $this->success($list);
    }


}