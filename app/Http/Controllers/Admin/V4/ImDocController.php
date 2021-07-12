<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\MallCategory;
use App\Models\WorksCategory;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;

class ImDocController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_doc/add 添加文案
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/add
     * @apiGroup 社群文案
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
     * @apiName  api/admin_v4/im_doc/list
     * @apiGroup 社群文案
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
     * @apiName  api/admin_v4/im_doc/change_status
     * @apiGroup 社群文案
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
     * @api {put} api/admin_v4/im_doc/job_add 添加发送任务
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/job_add
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/job_add
     * @apiParam {number} doc_id 文案id
     * @apiParam {number=1,2} send_type 发送时间类型(1立刻 2定时)
     * @apiParam {string} [send_at] 定时时间
     * @apiParam {string[]} info 对象列表
     * @apiParam {string=1,2,3} info.send_obj_type 目标对象类型(1群组 2个人 3标签)
     * @apiParam {string} info.send_obj_id 目标id
     * @apiDescription 添加发送任务
     * @apiParamExample {json} Request-Example:
     * {
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
            'works' => [
                'name' => '精品课',
                'category' => $works_category
            ],
            'lecture' => [
                'name' => '讲座'
            ],
            'goods' => [
                'name' => '商品',
                'category' => $goods_category
            ],
            'live' => [
                'name' => '直播训练营',
                'category' => [
                    [
                        'id' => '100001',
                        'name' => '直播'
                    ],
                    [
                        'id' => '100002',
                        'name' => '训练营'
                    ],
                    [
                        'id' => '100003',
                        'name' => '幸福360'
                    ],
                ]
            ]

        ];

        return success($data);
    }
}
