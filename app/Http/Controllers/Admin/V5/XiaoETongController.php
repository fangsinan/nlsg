<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\XiaoETongServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XiaoETongController extends ControllerBackend
{
    //推广员列表
    public function vipList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipList($request->input(), $this->user));
    }

    public function orderList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->orderList($request->input(), $this->user));
    }

    public function orderDistributeList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->orderDistributeList($request->input(), $this->user));
    }


    public function vipAdd(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipAdd($request->input(), $this->user));
    }

    public function vipBindUser(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipBindUser($request->input(), $this->user));
    }

    public function XeUserInfo(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipInfo($request->input(), $this->user));
    }

    public function userList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->userList($request->input(), $this->user));
    }


    public function vipListExcel(Request $request): JsonResponse
    {
        $page = 1;
        $params = $request->input();
        $params['size'] = 500;
        while (true){
            $params['page'] = $page;
            $data = (new XiaoETongServers())->vipList($params, $this->user);
            foreach ($data as $v){
                echo $v->id,PHP_EOL;
            }
            $page++;
        }

    }

    public function orderListExcel(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->orderList($request->input(), $this->user));
    }

    public function orderDistributeListExcel(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->orderDistributeList($request->input(), $this->user));
    }


}
