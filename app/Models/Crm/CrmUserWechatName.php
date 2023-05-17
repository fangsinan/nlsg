<?php

namespace App\Models\Crm;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CrmUserWechatName extends Base
{
    const DB_TABLE = 'nlsg_user_wechat_name';
    protected $table = 'nlsg_user_wechat_name';

    public function WechatDepartmentInfo(): HasOne
    {
        return $this->hasOne(CrmUserWechatDepartment::class,'id','department');
    }
}
