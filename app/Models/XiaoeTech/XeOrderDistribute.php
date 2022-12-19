<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class XeOrderDistribute extends Base
{
    const DB_TABLE = 'nlsg_xe_order_distribute';
    protected $table = 'nlsg_xe_order_distribute';

    public function shareUserInfo(): HasOne
    {
        return $this->hasOne(XeUser::class,'xe_user_id','share_user_id');
    }

    public function superiorDistributeUserInfo(): HasOne
    {
        return $this->hasOne(XeUser::class,'xe_user_id','superior_distribute_user_idv');
    }

}
