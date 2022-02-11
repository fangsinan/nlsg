<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\RecommendConfigServers as rcs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendConfigController extends ControllerBackend
{

    /**
     * 推荐位列表
     * @api {get} /api/admin_v5/recommend_config/list 推荐位列表
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/recommend_config/list
     * @apiGroup  后台-v5-推荐位
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/recommend_config/list
     * @apiDescription 推荐位列表
     * @apiParam {number} [title] 标题
     * @apiParam {number} [is_show] 是否显示(-1全部,0不显示,1显示)
     */
    public function list(Request $request): JsonResponse {
        return $this->getRes((new rcs())->list($request->input()));
    }

    public function sort(Request $request): JsonResponse {
        return $this->getRes((new rcs())->sort($request->input()));
    }

    /**
     * 添加推荐位
     * @api {post} /api/admin_v5/recommend_config/add 添加推荐位
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/recommend_config/add
     * @apiGroup  后台-v5-推荐位
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/recommend_config/add
     * @apiDescription 添加推荐位
     * @apiParam {number} title 标题
     * @apiParam {number} [icon_pic] icon图标
     * @apiParam {number} show_position 位置
     * @apiParam {number} jump_type 跳转类型
     * @apiParam {number} modular_type 模块类型
     * @apiParam {number} is_show 是否显示(0不显示,1显示)
     * @apiParam {string} [jump_url] 跳转地址
     */
    public function add(Request $request): JsonResponse {
        return $this->getRes((new rcs())->add($request->input()));
    }

    /**
     * 推荐位详情
     * @api {post} /api/admin_v5/recommend_config/info 推荐位详情
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/recommend_config/info
     * @apiGroup  后台-v5-推荐位
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/recommend_config/info
     * @apiDescription 推荐位详情
     * @apiParam {number} id 推荐位id
     */
    public function Info(Request $request): JsonResponse {
        return $this->getRes((new rcs())->Info($request->input()));
    }

    /**
     * 推荐位详情可添加课程的列表
     * @api {get} /api/admin_v5/recommend_config/info_select_list 推荐位详情可添加课程的列表
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/recommend_config/info_select_list
     * @apiGroup  后台-v5-推荐位
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/recommend_config/info_select_list
     * @apiDescription 推荐位详情可添加课程的列表
     * @apiParam {number} modular_type 推荐位的modular_type
     */
    public function infoSelectList(Request $request): JsonResponse {
        return $this->getRes((new rcs())->infoSelectList($request->input()));
    }

    /**
     * 推荐位详情绑定新元素
     * @api {get} /api/admin_v5/recommend_config/info_bind 推荐位详情绑定新元素
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/recommend_config/info_bind
     * @apiGroup  后台-v5-推荐位
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/recommend_config/info_bind
     * @apiDescription 推荐位详情绑定新元素
     * @apiParam {number} recommend_config_id 推荐位id
     * @apiParam {number} show_position 推荐位的show_position
     * @apiParam {number} jump_type 推荐位的jump_type
     * @apiParam {number} modular_type 推荐位的modular_type
     * @apiParam {number} obj_id 绑定元素的目标id
     * @apiParam {number} [recommend_info_id] 如果是编辑,传原来绑定元素的id
     */
    public function infoBind(Request $request): JsonResponse {
        return $this->getRes((new rcs())->infoBind($request->input()));
    }

    public function delInfoBind(Request $request): JsonResponse {
        return $this->getRes((new rcs())->delInfoBind($request->input()));
    }

}
