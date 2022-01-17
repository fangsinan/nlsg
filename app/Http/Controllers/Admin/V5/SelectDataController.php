<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\SelectDataServers as sds;
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
        return $this->getRes((new sds())->recommendTypeList($request->input()));
    }

    /**
     * 作品列表
     * @api {get} /api/admin_v5/select_data/works_list 作品列表
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/select_data/works_list
     * @apiGroup  后台-v5-下拉框数据
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/select_data/works_list
     * @apiDescription 作品列表
     */
    public function worksList(Request $request): JsonResponse {
        return $this->getRes((new sds())->worksList($request->input()));
    }

    /**
     * 作品合计列表
     * @api {get} /api/admin_v5/select_data/works_lists_list 作品合计列表
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/select_data/works_lists_list
     * @apiGroup  后台-v5-下拉框数据
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/select_data/works_lists_list
     * @apiDescription 作品合计列表
     */
    public function worksListsList(Request $request): JsonResponse {
        return $this->getRes((new sds())->worksListsList($request->input()));
    }

    /**
     * 老师列表
     * @api {get} /api/admin_v5/select_data/teacher_list 老师列表
     * @apiVersion 5.0.0
     * @apiName /api/admin_v5/select_data/teacher_list
     * @apiGroup  后台-v5-下拉框数据
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v5/select_data/teacher_list
     * @apiDescription 老师列表
     */
    public function teacherList(Request $request): JsonResponse {
        return $this->getRes((new sds())->teacherList($request->input()));
    }

}
