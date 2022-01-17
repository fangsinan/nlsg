<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\RecommendConfigServers as rcs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelectDataController extends ControllerBackend
{
    /**
     * 推荐位可选位置
     * @api {get} /api/admin_v5/select_data/recommend_type_list 推荐位可选位置
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/select_data/recommend_type_list
     * @apiGroup  后台-v5-下拉框数据
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/select_data/recommend_type_list
     * @apiDescription 推荐位可选位置
     */
    public function recommendTypeList(Request $request): JsonResponse {
        return $this->getRes((new rcs())->selectList($request->input()));
    }
}
