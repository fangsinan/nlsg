<?php


namespace App\Servers;


use App\Models\MallComment;
use App\Models\MallGoods;
use App\Models\MallSku;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MallCommentServers
{
    public function replyComment($params, $user_id)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => 'id不能为空'];
        }

        $check_id = MallComment::whereId($params['id'])->first();
        if (empty($check_id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        if (empty($params['content'] ?? '')) {
            $check_id->reply_comment = '';
            $check_id->reply_user_id = 0;
            $check_id->replied_at = null;
        } else {
            $check_id->reply_comment = $params['content'] ?? '';
            $check_id->reply_user_id = $user_id;
            $check_id->replied_at = date('Y-m-d H:i:s');
        }

        $res = $check_id->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }
    }

    public function changeStatus($params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => 'id不能为空'];
        }

        $check_id = MallComment::whereId($params['id'])->first();
        if (empty($check_id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($params['flag'] ?? '') {
            case 'on':
                $check_id->status = 1;
                break;
            case 'off':
                $check_id->status = 0;
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = $check_id->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    public function list($params)
    {

        $size = $params['size'] ?? 10;

        $query = MallComment::from('nlsg_mall_comment as c')
            ->join('nlsg_mall_goods as g', 'c.goods_id', '=', 'g.id')
            ->join('nlsg_mall_sku as s', 'c.sku_number', '=', 's.sku_number')
            ->join('nlsg_user as u', 'c.user_id', '=', 'u.id');

        if (!empty($params['id'] ?? 0)) {
            $query->where('c.id', '=', $params['id']);
        }

        if (!empty($params['content'] ?? '')) {
            $query->where('c.content', 'like', '%' . trim($params['content']) . '%');
        }

        if (!empty($params['goods_name'] ?? '')) {
            $query->where('g.name', 'lie', '%' . trim($params['goods_name']) . '%');
        }

        if (intval($params['is_robot'] ?? 0) === 1) {
            $query->where('u.is_robot', '=', 1);
        } else {
            $query->where('u.is_robot', '=', 0);
        }

        $query->with(['sku', 'sku.sku_value_list']);
        $query->orderBy('c.id', 'desc');

        $query->select([
            'c.id as comment_id', 'c.content', 'c.picture', 'c.star', 'g.name as goods_name',
            'g.picture as goods_picture', 'u.nickname', 'u.phone', 'c.sku_number', 'c.status',
        ]);

        $list = $query->paginate($size);

        return $list;
    }

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
            if (empty($v['content'])) {
                continue;
            }
            $temp_data = [];
            $temp_data['user_id'] = $robot[$key] ?? 0;
            $temp_data['content'] = $v['content'] ?? '';
            $temp_data['picture'] = $v['picture'] ?? '';
            $temp_data['goods_id'] = $goods_id;
            $temp_data['sku_number'] = $sku_number;
            $add_data[] = $temp_data;
        }

        $res = DB::table('nlsg_mall_comment')->insert($add_data);

        MallGoods::where('id', '=', $goods_id)->increment('sales_num_virtual', $count);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }
}
