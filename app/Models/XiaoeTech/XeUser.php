<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;
use App\Models\User;
use App\Models\VipUser;
use App\Models\VipUserBind;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class XeUser extends Base
{
    const DB_TABLE = 'nlsg_xe_user';
    protected $table = 'nlsg_xe_user';

    public function nlsgUserInfo(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function vipInfo(): HasOne
    {
        return $this->hasOne(VipUser::class,'username','phone')
            ->where('status','=',1)
            ->where('is_default','=',1);
    }

    public function vipBindInfo(): HasOne
    {
        return $this->hasOne(VipUserBind::class,'son','phone')
            ->where('status','=',1);
    }

    public function distributorInfo(): HasOne
    {
        return $this->hasOne(XeDistributor::class,'xe_user_id','xe_user_id');
    }

    public function parentList(): HasOne
    {
        return $this->hasOne(XeDistributorCustomer::class,'sub_user_id','xe_user_id')
            ->where('status','=',1);
    }

    public function sonList(): HasMany
    {
        return $this->hasMany(XeDistributorCustomer::class,'xe_user_id','xe_user_id')
            ->where('status','=',1);
    }

}
