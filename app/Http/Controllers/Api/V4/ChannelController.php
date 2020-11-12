<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Works;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * 课程列表
     * @api {get} /api/v4/channel/cytx 课程列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/cytx
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/cytx
     * @apiDescription 课程列表
     * */
    public function cytx(Request $request)
    {
        $model = new Works();
        $data = $model->listForCytx($request->input());
        return $this->getRes($data);
    }

}
