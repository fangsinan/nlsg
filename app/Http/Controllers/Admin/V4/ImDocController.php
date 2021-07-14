<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\MallCategory;
use App\Models\MallGoods;
use App\Models\Works;
use App\Models\Live;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImDocController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_doc/add 添加文案
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/add
     * @apiDescription 社群文案
     *
     * @apiParam {number=1,2,3} type 类型(1商品 2附件 3文本)
     * @apiParam {number} type_info 详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 21音频 22视频 23图片 31文本)
     * @apiParam {number} [obj_id]  目标id(当type=1时需要传)
     * @apiParam {string} content   内容或名称
     * @apiParam {string} [file_url]  附件地址,当type=2时需要传
     *
     */
    public function add(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->add($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {get} api/admin_v4/im_doc/list 文案列表
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/list
     * @apiDescription 社群文案
     */
    public function list(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc/change_status 文案状态修改
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_status
     * @apiParam {number} id id
     * @apiParam {string=del} flag 动作(del:删除)
     * @apiDescription 社群文案
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeStatus($request->input());
        return $this->getRes($data);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse{
     * "doc_id": 1,
     * "send_type": 1,
     * "send_at": "",
     * "info": [
     * {
     * "type": 1,
     * "list": [
     * 1,
     * 2,
     * 3
     * ]
     * }
     * ]
     * }
     */
    //添加发送任务
    public function addSendJob(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->addSendJob($request->input());
        return $this->getRes($data);
    }

    //发送任务列表
    public function sendJobList(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->sendJobList($request->input());
        return $this->getRes($data);
    }

    //发送任务状态修改
    public function changeJobStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeJobStatus($request->input());
        return $this->getRes($data);
    }

    /**
     * 选择商品分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategory()
    {
        $works_category = WorksCategory::select('id', 'name', 'pid', 'level', 'sort')
            ->where(['status' => 1,])
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();

        $mall = new MallCategory();
        $goods_category = $mall->getUsedList();

        $data = [
            'works'   => [
                'type'     => 1,
                'name'     => '精品课',
                'category' => $works_category
            ],
            'lecture' => [
                'type' => 2,
                'name' => '讲座'
            ],
            'goods'   => [
                'type'     => 3,
                'name'     => '商品',
                'category' => $goods_category
            ],
            'live'    => [
                'name'     => '直播训练营',
                'category' => [
                    [
                        'id'   => '100001',
                        'type' => 4,
                        'name' => '直播'
                    ],
                    [
                        'id'   => '100002',
                        'type' => 5,
                        'name' => '训练营'
                    ],
                    [
                        'id'   => '100003',
                        'type' => 6,
                        'name' => '幸福360'
                    ],
                ]
            ]

        ];

        return success($data);
    }


    /**
     * @api {get} api/admin_v4/im_doc/category/product 分类筛选的列表
     * @apiVersion 4.0.0
     * @apiName  im_doc
     * @apiGroup 后台-分类筛选的列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/category/product
     * @apiDescription 分类筛选的列表
     *
     * @apiParam {number} category_id 分类id 0为全部
     * @apiParam {number} type   类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360
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
    public function getCategoryProduct(Request $request)
    {
        $category_id = $request->get('category_id'); //0 为全部

        $type = $request->get('type');
        switch ($type) {
            case 1:

                $cate_id_arr = [];
                $cate_data = WorksCategory::find($category_id);
                if ($cate_data['level'] == 1) {
                    $cate_arr = WorksCategory::select('id')->where(['pid'    => $cate_data['id'], 'status' => 1
                    ])->get()->toArray();
                    $cate_id_arr = array_column($cate_arr, 'id');
                }

                $where = [
                    'works.status'        => 4,
                    'works.type'          => 2,
                    'works.is_audio_book' => 0
                ];
                $relationObj = new WorksCategoryRelation();
                $worksObj = new Works();
                $query = DB::table($relationObj->getTable(), ' relation')
                    ->leftJoin($worksObj->getTable().' as works', 'works.id', '=', 'relation.work_id')
                    ->select('works.id', 'works.type', 'works.title', 'works.user_id', 'works.cover_img', 'works.price',
                        'works.original_price', 'works.subtitle',
                        'works.works_update_time', 'works.detail_img', 'works.content', 'relation.id as relation_id',
                        'relation.category_id', 'relation.work_id', 'works.column_id',
                        'works.comment_num', 'works.chapter_num', 'works.subscribe_num', 'works.collection_num',
                        'works.is_free');
                if ($cate_id_arr && $category_id != 0) {
                    $query->whereIn('relation.category_id', $cate_id_arr);
                }

                $lists = $query->where($where)
                    ->orderBy('works.created_at', 'desc')
                    ->groupBy('works.id')
                    ->paginate(10)
                    ->toArray();

                break;
            case 2:
                $lists = Column::select('id', 'user_id', 'name', 'title', 'subtitle', 'cover_img', 'price', 'status',
                    'created_at',
                    'info_num')
                    ->where('type', 2)
                    ->where('status', '<>', 3)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case  3:
                $query = MallGoods::query();
                if ($category_id != 0) {
                    $query->where('category_id', $category_id);
                }
                $lists = $query->select('id', 'name', 'subtitle', 'picture', 'status')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case 4:
                $lists = Live::select('id', 'user_id', 'title', 'price', 'order_num', 'status', 'begin_at', 'cover_img')
                    ->where('is_del', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case 5:
                $lists = Column::select('id', 'user_id', 'name', 'title', 'subtitle', 'cover_img', 'price', 'status',
                    'created_at',
                    'info_num')
                    ->where('type', 3)
                    ->where('status', '<>', 3)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;

        }

        return $lists ?? [];
    }
}
