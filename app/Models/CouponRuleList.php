<?php


namespace App\Models;


class CouponRuleList extends Base
{
    protected $table = 'nlsg_coupon_rule_list';

    public function list($id, $uid)
    {
        if (empty($id ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $data = CouponRuleList::find($id);

        if (empty($data)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $rule_id_list = explode(',', $data->rule_id_list);

        $crModel = new CouponRule();
        return $crModel->getList([
            'id_list' => $rule_id_list
        ], $uid);

    }
}
