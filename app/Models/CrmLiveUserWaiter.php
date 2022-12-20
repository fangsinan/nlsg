<?php


namespace App\Models;


class CrmLiveUserWaiter extends Base
{
    const DB_TABLE = 'crm_live_user_waiter';
    protected $table = 'crm_live_user_waiter';

    public function adminUserInfo()
    {
        return $this->hasOne(CrmAdminUser::class, 'id', 'admin_id');
    }

}
