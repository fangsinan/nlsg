<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class XeDistributor extends Base
{
    const DB_TABLE = 'nlsg_xe_distributor';
    protected $table = 'nlsg_xe_distributor';

    public function XeUserInfo(): HasOne
    {
        return $this->hasOne(XeUser::class,'xe_user_id','xe_user_id');
    }

    public function XeUserParentInfo(): HasOne
    {
        return $this->hasOne(XeUser::class,'xe_user_id','xe_parent_user_id');
    }

}
