<?php


namespace App\Models;

class CashData extends Base
{
    protected $table = 'nlsg_cash_data';

    protected $fillable = [
        'user_id', 'idcard', 'tax_deduction', 'truename', 'idcard_cover', 'zfb_account', 'phone', 'reason',
        'default_account', 'is_pass', 'wx_account', 'WxNickName', 'app_wx_account', 'app_WxNickName',
        'balance2017_cash_time',
        'type', 'org_type', 'org_name', 'org_area', 'org_address', 'org_license_picture', 'bank_opening', 'bank_number',
        'bank_permit_picture', 'log','app_project_type',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

}
