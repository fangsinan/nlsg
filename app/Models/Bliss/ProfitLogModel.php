<?php
namespace App\Models\Bliss;

use App\Models\Base;
use App\Models\User;

/**
 * 收益日志消息
 */
class ProfitLogModel extends Base
{
    const DB_TABLE = 'xfxs_profit_log';
    protected $table = 'xfxs_profit_log';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
