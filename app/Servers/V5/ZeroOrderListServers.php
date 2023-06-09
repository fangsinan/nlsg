<?php


namespace App\Servers\V5;


use App\Models\LiveCrm\CrmLiveWaiterWechat;
use App\Models\OrderZero;
use App\Models\User\UserWechat;
use App\Servers\LiveInfoServers;
use Illuminate\Support\Facades\DB;

class ZeroOrderListServers
{
    public function list($params, $this_user = [], $is_excel = 0)
    {
        $query = OrderZero::query()
            ->with([
                'user:id,phone,nickname,unionid',
                'twitter:id,phone,nickname',
                'relationLiveInfo:id,title,cover_img,price,is_zero',
                'fromLiveInfo:id,title,cover_img,price,is_zero',
            ]);


        if ($this_user['role_id'] !== 1) {
            $liServers       = new LiveInfoServers();
            $twitter_id_list = $liServers->twitterIdList($this_user['username']);
            if ($twitter_id_list !== null) {
                $query->whereIn('twitter_id', $twitter_id_list);
            }

            $query->where(function ($q) {
                $q->where('is_show', '=', 2)->orWhere('is_wechat', '=', 2);
            });
        }

        $phone          = $params['phone'] ?? '';
        $twitter_phone  = $params['twitter_phone'] ?? '';
        $relation_id    = $params['relation_id'] ?? 0;
        $live_id        = $params['live_id'] ?? 0;
        $pay_time_begin = $params['pay_time_begin'] ?? '';
        $pay_time_end   = $params['pay_time_end'] ?? '';
        $order_num      = $params['ordernum'] ?? '';
        $relation_title = $params['relation_title'] ?? '';
        $live_title     = $params['live_title'] ?? '';
        $page           = $params['page'] ?? 1;
        $size           = $params['size'] ?? 10;

        $query->whereHas('user', function ($q) {
            $q->where('is_test_pay', '=', 0);
        });

        if ($phone) {
            $query->whereHas('user', function ($q) use ($phone) {
                $q->where('phone', 'like', $phone);
            });
        }
        if ($twitter_phone) {
            $query->whereHas('twitter', function ($q) use ($twitter_phone) {
                $q->where('phone', 'like', $twitter_phone);
            });
        }
        if ($relation_title) {
            $query->whereHas('relationLiveInfo', function ($q) use ($relation_title) {
                $q->where('title', 'like', $relation_title);
            });
        }
        if ($live_title) {
            $query->whereHas('fromLiveInfo', function ($q) use ($live_title) {
                $q->where('title', 'like', $live_title);
            });
        }

        if ($relation_id) {
            $query->where('relation_id', '=', $relation_id);
        }
        if ($live_id) {
            $query->where('live_id', '=', $live_id);
        }
        if ($pay_time_begin) {
            $query->where('pay_time', '>=', $pay_time_begin);
        }
        if ($pay_time_end) {
            $query->where('pay_time', '<=', $pay_time_end);
        }
        if ($order_num) {
            $query->where('ordernum', 'like', $order_num);
        }


        $query->select([
            'id', 'relation_id', 'live_id', 'user_id', 'status', 'pay_time', 'ordernum', 'twitter_id', 'is_wechat'
        ]);

        $query->orderBy('id', 'desc');

        if ($is_excel) {
            $excel_list =  $query->offset(($page - 1) * $size)->limit($size)->get();
            if ($this_user['role_id'] == 1) {
                foreach ($excel_list as $v){
                    if ($v->is_wechat === 2 &&$v->user->unionid ){
                        $v->admin_wechat = $this->getOldBindWechatWaiter($v->user->unionid);
                    }
                }
            }
            return $excel_list;
        }

        return $query->paginate($size);
    }

    public function getOldBindWechatWaiter($unionid)
    {
        return  DB::table('nlsg_user_wechat as uw')
            ->join('crm_live_waiter_wechat as ww',
                'uw.follow_user_userid', '=', 'ww.follow_user_userid'
            )
            ->join('crm_admin_user as au','ww.admin_id','=','au.id')
            ->where('uw.unionid', '=', $unionid)
            ->select([
                'ww.follow_user_userid', 'uw.follow_user_createtime as bind_admin_time',
                'ww.admin_id','au.name'
            ])
            ->first();
    }
}
