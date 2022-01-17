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
     * @api {get} /api/admin_v5/recommend_config/add 添加推荐位
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

    public function Info(Request $request): JsonResponse {
        return $this->getRes((new rcs())->Info($request->input()));
    }

}
