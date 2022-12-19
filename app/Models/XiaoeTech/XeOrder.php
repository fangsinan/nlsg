<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

class XeOrder extends Base
{
    const DB_TABLE = 'nlsg_xe_order';

    protected $table = 'nlsg_xe_order';

    public function xeUserInfo(): HasOne
    {
        return $this->hasOne(XeUser::class,'xe_user_id','xe_user_id');
    }

    public function userInfo(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
