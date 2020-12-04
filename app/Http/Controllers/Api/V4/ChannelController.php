<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Works;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * 创业天下课程列表
     * @api {get} /api/v4/channel/cytx 创业天下课程列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/cytx
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/cytx
     * @apiDescription 创业天下课程列表
     * */
    public function cytx(Request $request)
    {
        $model = new Works();
        $data = $model->listForCytx($request->input());
        return $this->getRes($data);
    }


    public function click(Request $request){
        $ip = Request::createFromGlobals()->getClientIp();
        dd($ip);
        $model = new Click();
        $data = $model->add($request->input(),$this->user,$request->ip());
        return $this->getRes($data);
    }


}
