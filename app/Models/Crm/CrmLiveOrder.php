<?php

namespace App\Models\Crm;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CrmLiveOrder extends Base
{
    const DB_TABLE = 'crm_live_order';
    protected $table = 'crm_live_order';

    public function AdminUserInfo(): HasOne
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id');
    }

    public function AdminWechatInfo(): HasOne
    {
        return $this->hasOne(
            CrmUserWechatName::class,
            'follow_user_userid',
            'follow_user_userid'
        );
    }

    public function getKeFuInfoByUidLid($uid,$lid){
        $query = self::query()
            ->with([
                       'AdminUserInfo:id,name',
                       'AdminWechatInfo',
                       'AdminWechatInfo.WechatDepartmentInfo',
                   ])
            ->where('user_id','=',$uid)
            ->where('live_id','=',$lid)
            ->where('status','=',1)
            ->select(['id','live_id','user_id','admin_id','follow_user_userid']);

        return $query->first();
    }

}
