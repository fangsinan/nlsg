<?php


namespace App\Servers;

use App\Models\ImUserFriend;
use App\Models\MallOrder;
use App\Models\Order;
use App\Models\User;

class ImUserServers
{
    public function list($params, $user_id)
    {
        $size = $params['size'] ?? 10;

        $query = User::query();

        //详情
        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }

        if (!empty($params['phone']) ?? '') {
            $query->where('phone', 'like', '%' . $params['phone'] . '%');
        }

        //性别
        $sex = $params['sex'] ?? -1;
        if ($sex >= 0) {
            $query->where('sex', '=', intval($params['sex']));
        }
        //会员
        switch (intval($params['vip'] ?? 0)) {
            case 1:
                //不是会员
                $query->doesntHave('vipUser');
                break;
            case 2:
                //是会员
                $query->has('vipUser');
                break;
            case 21:
                //幸福大使
                $query->whereHas('vipUser', function ($q) {
                    $q->where('level', '=', 1);
                });
                break;
            case 22:
                //钻石会员
                $query->whereHas('vipUser', function ($q) {
                    $q->where('level', '=', 2);
                });
                break;

        }
        //订单(1已完成,2未完成)
        $order_type = intval($params['order_type'] ?? 0);
        if (!empty($order_type)) {
            $now = date('Y-m-d H:i:s');
            $not_pay_user_list = MallOrder::query()
                ->where('status', '=', 1)
                ->where('is_del', '=', 0)
                ->where('is_stop', '=', 0)
                ->where('dead_time', '>', $now)
                ->pluck('user_id')
                ->toArray();

            switch ($order_type) {
                case 1:
                    //已完成
                    if (!empty($not_pay_user_list)) {
                        $query->whereNotIn('id', $not_pay_user_list);
                    }
                    break;
                case 2:
                    //未完成
                    $query->whereIn('id', $not_pay_user_list);
                    break;
            }
        }


        //注册时间
        $created_at = (string)($params['created_at'] ?? '');
        $begin_date = '';
        $end_date = '';
        switch ($created_at) {
            case '':
                break;
            case '1':
                $begin_date = date('Y-m-d 00:00:00', strtotime('-1 month'));
                break;
            case '2':
                $begin_date = date('Y-m-d 00:00:00', strtotime('-3 month'));
                break;
            default:
                if (!is_numeric($created_at)) {
                    $temp_date = explode(',', $created_at);
                    if (is_array($temp_date) && count($temp_date) == 2) {
                        $begin_date = $temp_date[0];
                        $end_date = $temp_date[1];
                    }
                }
                break;
        }
        if (!empty($begin_date)) {
            $query->where('created_at', '>=', $begin_date);
        }
        if (!empty($end_date)) {
            $query->where('created_at', '<', $end_date);
        }

        $query->with([
            'imUser:id,tag_im_nick,tag_im_gender,tag_im_image,tag_im_to_account',
            'vipUser:id,user_id,level,is_open_360,created_at,expire_time,time_begin_360,time_end_360',
        ]);


        //序号,昵称,账号,头像,性别,会员,注册时间
        $query->select([
            'id', 'phone', 'nickname', 'headimg', 'sex', 'created_at', 'birthday',
            'intro', 'is_staff', 'status', 'ios_balance', 'is_author', 'income_num',
            'reply_num', 'income_num', 'fan_num', 'follow_num', 'fans_num', 'ref', 'is_test_pay'
        ]);

        $res['list'] = $query->where('is_robot', '=', 0)->paginate($size);

        foreach ($res['list'] as &$v) {
            $v->open_count = Order::query()
                ->where('user_id', '=', $v->id)
                ->where('type', '=', 16)
                ->where('status', '=', 1)
                ->count();
        }

        $res['statistics'] = $this->userStatistics();
        return $res;
    }

    public function userStatistics($begin_date = '', $end_date = '')
    {
        $query = User::query()
            ->where('is_robot','=',0)
            ->where('status','=',1);

//        if (!empty($begin_date)) {
//            $query->where('created_at', '>=', $begin_date);
//        }
//        if (!empty($end_date)) {
//            $query->where('created_at', '<', $end_date);
//        }

        $res['all'] = (clone $query)->count();
        $res['man'] = (clone $query)->where('sex', '=', 1)->count();
        $res['woman'] = (clone $query)->where('sex', '=', 2)->count();
//        $res['unknown'] = (clone $query)->where('sex', '=', 0)->count();
        $res['unknown'] = (clone $query)->whereNotIn('sex',[1,2])->count();
        return $res;
    }

    public function friendsList($params, $user_id)
    {
        $size = $params['size'] ?? 10;
        $phone = $params['phone'] ?? '';

        $query = ImUserFriend::query()
            ->where('from_account', '=', $user_id)
            ->where('status', '=', 1);

        $query->with(['UserInfo:id,phone,nickname'])->has('UserInfo');
        $query->with(['ImUser:tag_im_to_account,tag_im_image,tag_im_nick'])->has('UserInfo');

        if (!empty($phone)){
            $query->whereHas('UserInfo',function($q)use($phone){
                $q->where('phone','like',"%$phone%");
            });
        }

        $query->select([
            'id', 'from_account', 'from_name', 'to_account', 'to_name', 'created_at'
        ]);

        return ['list'=>$query->paginate($size),'count'=>$query->count(),];

    }

    public function orderList($params, $user_id)
    {
        $size = $params['size'] ?? 10;
        $query = Order::query()->where('status', '=', 1);
        if (!empty($params['user_id'] ?? 0)) {
            $user_id = $params['user_id'];
        }
        $query->where('user_id', '=', $user_id);
        $query->where('is_shill', '=', 0);
        //9精品课  10直播  14 线下产品(门票类)   15讲座  16新vip  18训练营
        $query->whereIn('type', [9, 10, 14, 15, 16, 18]);
        $query->with([
            'works:id,type,title,cover_img',
            'works.categoryRelation',
            'works.categoryRelation.categoryName',
            'column:id,type,name,cover_pic',
            'live:id,title,cover_img',
            'offline:id,title,cover_img'
        ]);

        $query->select(['id', 'type', 'relation_id', 'live_id', 'user_id', 'status', 'pay_time',
            'ordernum', 'pay_price']);

        $list = $query->paginate($size);

        foreach ($list as $v) {
            $v->category_name = '';
            switch (intval($v->type)) {
                case 9:
                    $v->type_name = '课程';
                    $v->cover_img = $v->works->cover_img ?? '';
                    $v->title = $v->works->title ?? '-';
                    $temp_category_name = [];
                    foreach ($v->works->categoryRelation as $vv) {
                        $temp_category_name[] = $vv->categoryName['name'];
                    }
                    $v->category_name = implode(',', $temp_category_name);
                    break;
                case 10:
                    $v->type_name = '直播';
                    $v->cover_img = $v->live->cover_img ?? '';
                    $v->title = $v->live->title ?? '-';
                    break;
                case 14:
                    $v->type_name = '线下课';
                    $v->cover_img = $v->offline->cover_img ?? '';
                    $v->title = $v->offline->title ?? '-';
                    break;
                case 15:
                    $v->type_name = '讲座';
                    $v->cover_img = $v->column->cover_img ?? '';
                    $v->title = $v->column->name ?? '-';
                    break;
                case 16:
                    $v->type_name = 'vip';
                    $v->cover_img = '/nlsg/works/20210105102849884378.png';
                    $v->title = 'vip';
                    break;
                case 18:
                    $v->type_name = '训练营';
                    $v->cover_img = $v->column->cover_img ?? '';
                    $v->title = $v->column->name ?? '-';
                    break;
                default:
                    $v->type_name = '-';
                    $v->cover_img = '';
                    $v->title = '';
            }
            unset(
                $v->works,
                $v->column,
                $v->live,
                $v->offline
            );
        }

        return $list;
    }

    public function mallOrderList($params, $user_id)
    {
        $servers = new MallOrderServers();
        if (empty($params['user_id'] ?? 0)){
            return ['code'=>false,'msg'=>'用户错误'];
        }
        return $servers->listNew($params, $user_id);
    }

    public function rechargeOrder($params,$user_id){
        if (empty($params['user_id'] ?? 0)){
            return ['code'=>false,'msg'=>'用户错误'];
        }
        $size = $params['size'] ?? 10;

        $query = Order::query()
            ->where('user_id','=',$params['user_id'])
            ->where('type','=',3)
            ->where('status','=',1);
        $query->select(['id','user_id','pay_time','pay_price','status']);

        return $query->paginate($size);
    }



}
