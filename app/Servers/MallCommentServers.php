<?php


namespace App\Servers;


use App\Models\MallComment;
use App\Models\MallSku;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MallCommentServers
{
    public function addRobotComment($params)
    {
        $goods_id = $params['goods_id'] ?? 0;
        $sku_number = $params['sku_number'] ?? 0;
        $list = $params['list'] ?? [];
        if (empty($goods_id) || empty($sku_number) || empty($list)) {
            return [
                'code' => false,
                'msg' => '参数错误',
            ];
        }

        $check_goods = MallSku::where('sku_number', '=', $sku_number)
            ->where('goods_id', '=', $goods_id)
            ->first();
        if (empty($check_goods)) {
            return ['code' => false, 'msg' => '商品数据错误'];
        }

        $count = count($list);
        $robot = User::where('is_robot', '=', 1)
            ->select(['id'])
            ->orderByRaw('rand()')
            ->limit($count)
            ->get()->toArray();
        $robot = array_column($robot, 'id');

        $add_data = [];
        foreach ($list as $key => $v) {
            if (empty($v['comment'])) {
                continue;
            }
            $temp_data = [];
            $temp_data['user_id'] = $robot[$key] ?? 0;
            $temp_data['content'] = $v['comment'] ?? '';
            $temp_data['picture'] = $v['picture'] ?? '';
            $temp_data['goods_id'] = $goods_id;
            $temp_data['sku_number'] = $sku_number;
            $add_data[] = $temp_data;
        }

        $res = DB::table('nlsg_mall_comment')->insert($add_data);
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }
}
