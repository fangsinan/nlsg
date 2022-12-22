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
    {
        set_time_limit(1200);

        $title = [
            '小鹅通id', '手机', '昵称', '状态', '保护人', '钻石合伙人', '分组信息', '来源',
        ];

        $csv = new CsvHelper($title);

        $page           = 1;
        $params         = $request->input();
        $params['size'] = 100;

        while (true) {

            $request->offsetSet('page', $page);

            $data = (new XiaoETongServers())->vipList($params, $this->user);
            if ($data->isEmpty()) {
                break;
            }

            $arr = [];

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

            $csv->insert($arr);

            $page++;
        }

        $csv->end();

    }

    public function orderListExcel(Request $request): JsonResponse
    {
        set_time_limit(1200);

        $title = [
            '订单号', '商品名称', '订单类型', '购买手机号', '昵称', '支付金额', '成为合伙人时间',
            '分享人', '保护人', '钻石合伙人', '状态', '能量时光客服', '订单时间'
        ];

        $csv = new CsvHelper($title);

        $page           = 1;
        $params         = $request->input();
        $params['size'] = 100;

        while (true) {
            $request->offsetSet('page', $page);

            $data = (new XiaoETongServers())->orderList($params, $this->user);

            if ($data->isEmpty()) {
                break;
            }

            $arr = [];

            foreach ($data as $v) {
                $temp_arr = [
                    'order_id'    => $v->order_id,
                    'goods_name'  => implode(',', array_column($v->orderGoodsInfo->toArray(), 'goods_name')),
                    'order_type'  => $v->order_type_desc,
                    'phone'       => $v->xeUserInfo['phone'] ?? '-',
                    'nickname'    => $v->xeUserInfo['nickname'] ?? '-',
                    'pay_price'   => $v->actual_price ? $v->actual_price / 100 : 0,
                    'created_at'  => $v->xe_created_time,
                    'share_user'  => $v->distributeInfo->shareUserInfo->nickname ?? '-',
                    'bind_user'   => $v->xeUserInfo->vipBindInfo['parent'] ?? '-',
                    'source_user' => $v->xeUserInfo->vipInfo->sourceVipInfo['username'] ?? '-',
                    'order_state' => $v->order_state_desc,
                    'waiter_user' => $v->xeUserInfo->liveUserWaiterInfo->adminUserInfo['name'] ?? '-',
                    'pay_time'    => $v->pay_state_time ?: $v->order_state_time,
                ];
                $arr[]    = $temp_arr;
            }

            $csv->insert($arr);

            $page++;
        }

        $csv->end();
    }

    public function orderDistributeListExcel(Request $request): JsonResponse
    {
        set_time_limit(1200);

        $title = [
            '订单号', '推广员id', '推广人昵称', '推广员手机号', '金额', '时间'
        ];

        $csv = new CsvHelper($title);

        $page           = 1;
        $params         = $request->input();
        $params['size'] = 100;

        while (true) {

            $request->offsetSet('page', $page);

            $data = (new XiaoETongServers())->orderDistributeList($params, $this->user);
            if ($data->isEmpty()) {
                break;
            }

            $arr = [];

            foreach ($data as $v) {
                $temp_arr = [
                    'order_id' => $v->order_id,
                    'user_id'  => $v->share_user_id,
                    'nickname' => $v->share_user_nickname,
                    'phone'    => $v->shareUserInfo['phone'],
                    'price'    => $v->distribute_price / 100,
                    'date'     => $v->created_at,
                ];
                $arr[]    = $temp_arr;
            }

            $csv->insert($arr);

            $page++;
        }

        $csv->end();
    }


}
