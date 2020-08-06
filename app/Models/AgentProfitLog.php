<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AgentProfitLog extends Base
{

    protected $table = 'agent_profit_log';

    protected  $fillable = ['ordernum','user_id','type','vip_type','column_id',
       'goods_id' ,'works_id','num','price','status'];


}
