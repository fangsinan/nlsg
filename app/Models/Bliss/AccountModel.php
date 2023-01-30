<?php
namespace App\Models\Bliss;

use App\Models\Base;

/**
 * 账号管理
 */
class AccountModel extends Base
{
    const DB_TABLE = 'xfxs_account';
    protected $table = 'xfxs_account';
    protected $fillable=[
        'user_id','username','bank_name','card_no','IDnumber','IDcard',
    ];
}
