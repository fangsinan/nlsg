<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class MeetingSalesBind extends Base
{

    protected $table = 'nlsg_meeting_sales_bind';

    public function bindDealerRecord($params, $user_id)
    {
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $check = MeetingSales::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->select(['id', 'user_id', 'phone', 'nickname', 'qr_code'])
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '没有权限'];
        }

        $list = MeetingSalesBind::query()
            ->where('sales_id', '=', $check->id)
            ->orderBy('id','desc')
//            ->orderBy('status', 'asc')
//            ->orderBy('end_at', 'desc')
//            ->withCount(['order' => function ($q) {
//                $q->where('status', '=', 1)->where('type', '=', 16);
//            }])
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        foreach ($list as $v) {
            $v->order_count = DB::table('nlsg_order as o')
                ->join('nlsg_pay_record_detail as d', 'o.ordernum', '=', 'd.ordernum')
                ->where('d.user_id', '=', $user_id)
                ->where('o.sales_bind_id', '=', $v->id)
                ->where('o.status', '=', 1)
                ->count();
        }

        $res['list'] = $list;
        $res['bind_count'] = MeetingSalesBind::where('sales_id', '=', $check->id)->count();
        $res['dealer_count'] = MeetingSalesBind::where('sales_id', '=', $check->id)
            ->groupBy('dealer_vip_id')
            ->get()
            ->count();

        return $res;
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'sales_bind_id', 'id')
            ->where('status', '=', 1);
    }

    public function bindDealer($params, $user_id)
    {
        if (empty($params['remark'] ?? '')) {
            return ['code' => false, 'msg' => '场次信息必填'];
        }

        if (empty($params['dealer_name'] ?? '')) {
            return ['code' => false, 'msg' => '经销商名称必填'];
        }

        $check = MeetingSales::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->select(['id', 'user_id', 'phone', 'nickname', 'qr_code'])
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '没有权限'];
        }

        $dealer_phone = $params['dealer_phone'] ?? 0;
        if (empty($dealer_phone)) {
            return ['code' => false, 'msg' => '经销商账号错误'];
        }

        $now = time();
        $now_date = date('Y-m-d H:i:00', $now);
        $end_date = date('Y-m-d H:i:00', strtotime("+1 days"));


        $check_dealer = VipUser::where('username', '=', $dealer_phone)
            ->where('level', '=', 2)
            ->where('is_default', '=', 1)
            ->where('status', '=', 1)
            ->where('start_time', '<=', $now_date)
            ->where('expire_time', '>=', $now_date)
            ->first();

        if (empty($check_dealer)) {
            return ['code' => false, 'msg' => '该账号不是经销商'];
        }

        //取消编辑
//        if (empty($params['id']??0)){
        $m = new MeetingSalesBind();
//        }else{
//            $m = MeetingSalesBind::where('id','=',$params['id'])
//                ->where('sales_id','=',$check->id)
//                ->first();
//            if (empty($m)){
//                return ['code'=>false,'msg'=>'id错误'];
//            }
//        }

        $m->sales_id = $check->id;
        $m->dealer_phone = $check_dealer->username;
        $m->dealer_vip_id = $check_dealer->id;
        $m->dealer_user_id = $check_dealer->user_id;
        $m->begin_at = $now_date;
        $m->end_at = $end_date;
        $m->remark = $params['remark'];
        $m->dealer_name = $params['dealer_name'];
        $m->status = 1;

        DB::beginTransaction();

        $m_res = $m->save();
        if ($m_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '添加失败,请重试' . __LINE__];
        }

        $update_res = MeetingSalesBind::where('sales_id', '=', $check->id)
            ->where('id', '<>', $m->id)
            ->update(['status' => 2]);
        if ($update_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '添加失败,请重试' . __LINE__];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

}
