<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\CsvHelper;
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

    public function vipUnbindUser(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipUnbindUser($request->input(), $this->user));
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
    {dd(__LINE__);
        $csv_helper = new CsvHelper();


        $title = [
            '小鹅通id', '手机', '昵称', '状态', '保护人', '钻石合伙人', '分组信息', '来源',
        ];

        $csv_helper->init($title);
        dd(__LINE__);
        $page           = 1;
        $params         = $request->input();
        $params['size'] = 100;


        while (true) {

            $params['page'] = $page;
            $data           = (new XiaoETongServers())->vipList($params, $this->user);
            $arr            = [];
            foreach ($data as $v) {
                $temp_arr = [
                    'xe_user_id' => $v->xe_user_id,
                    'phone'      => $v->XeUserInfo['phone'] ?? '-',
                    'nickname'   => $v->XeUserInfo['nickname'] ?? '-',
                    'status'     => $v->status == 1 ? '有效' : '无效',
                    'bind'       => $v->XeUserInfo->vipBindInfo['parent'] ?? '-',
                    'vip_source' => $v->XeUserInfo->vipInfo->sourceVipInfo['phone'] ?? '-',
                    'group_name' => $v->group_name ?? '-',
                ];

                switch ($v->source) {
                    case 1:
                        $temp_arr['source'] = 'API';
                        break;
                    case 2:
                        $temp_arr['source'] = '能量时光';
                        break;
                    case 3:
                        $temp_arr['source'] = '慧宇';
                        break;
                    case 4:
                        $temp_arr['source'] = '种子推广员';
                        break;
                    default:
                        $temp_arr['source'] = '-';
                        break;

                }
                $arr[] = $temp_arr;
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
