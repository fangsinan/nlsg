<?php


namespace App\Servers;


use App\Models\Column;
use App\Models\RedeemCode;
use App\Models\Works;
use Illuminate\Support\Facades\DB;

class RedeemCodeServers
{
    public function create($params, $admin_id)
    {
        $redeem_type = $params['redeem_type'] ?? 0;
        $goods_id = $params['goods_id'] ?? 0;
        $number = $params['number'] ?? 0;
        if (empty($goods_id) || empty($number)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if ($number > 1000) {
            return ['code' => false, 'msg' => '一次最多生成一千张'];
        }

        switch (intval($redeem_type)) {
            case 2:
                //精品课
                $check = Works::where('id', '=', $goods_id)
                    ->where('type', '=', 2)
                    ->select(['id', 'title'])
                    ->first();
                break;
            case 3:
                //讲座
                $check = Column::where('id', '=', $goods_id)
                    ->where('type', '=', 2)
                    ->select(['id', 'name as title'])
                    ->first();
                break;
            default:
                return ['code' => false, 'msg' => '类型错误'];
        }

        if (empty($check)) {
            return ['code' => false, 'msg' => ($redeem_type == 2 ? '课程' : '讲座') . '不存在'];
        }

        $group_name = RedeemCode::createGroupName();
        $code_name = $check->title . '-兑换券';
        $add_code_data = [];

        $i = 1;
        while ($i <= $number) {
            $add_code_data[] = [
                'code' => $group_name . RedeemCode::get_34_Number(RedeemCode::createCodeTemp(), 5),
                'name' => $code_name,
                'new_group' => $group_name,
                'redeem_type' => $redeem_type,
                'goods_id' => $goods_id,
                'is_new_code' => 1,
            ];
            $i++;
        }

        DB::beginTransaction();
        $res = DB::table('nlsg_redeem_code')->insert($add_code_data);

        if ($res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        } else {
            DB::commit();
            return ['code' => true, 'msg' => '成功' . $group_name];
        }
    }
}
