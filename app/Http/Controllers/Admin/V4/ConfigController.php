<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ConfigServers;
use Illuminate\Http\Request;

class ConfigController extends ControllerBackend
{

    /**
     * 商城搜索词热词列表
     * @api {get} /api/admin_v4/config/mall_keywords 商城搜索词热词列表
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/config/mall_keywords
     * @apiGroup  后台-设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/config/mall_keywords
     * @apiDescription 商城搜索词热词列表
     * @apiSuccess {number} id id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "keywords": [
     * "王琨",
     * "教育",
     * "育儿",
     * "孩子",
     * "玩具",
     * "夫妻关系",
     * "成长",
     * "教具",
     * "亲子",
     * "心理学"
     * ],
     * "hot_words": [
     * {
     * "on_fire": 1,
     * "val": "王琨"
     * }
     * ]
     * }
     */
    public function mallKeywords(Request $request)
    {
        $servers = new ConfigServers();
        $data = $servers->mallKeywords($request->input());
        return $this->getRes($data);
    }

    /**
     * 修改商城搜索词热词
     * @api {post} /api/admin_v4/config/edit_mall_keywords 修改商城搜索词热词
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/config/edit_mall_keywords
     * @apiGroup  后台-设置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/config/edit_mall_keywords
     * @apiDescription 修改商城搜索词热词
     * @apiParamExample {json} Request-Example:
     * {
     * "keywords": [
     * "王琨",
     * "教育",
     * "育儿",
     * "孩子",
     * "玩具",
     * "夫妻关系",
     * "成长",
     * "教具",
     * "亲子",
     * "心理学"
     * ],
     * "hot_words": [
     * {
     * "on_fire": 1,
     * "val": "王琨"
     * }
     * ]
     * }
     */
    public function editMallKeywords(Request $request)
    {
        $servers = new ConfigServers();
        $data = $servers->editMallKeywords($request->input());
        return $this->getRes($data);
    }


}
