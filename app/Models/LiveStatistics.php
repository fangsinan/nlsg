<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStatistics extends Model
{
    protected $table = 'nlsg_live_statistics';

    public static function getCounts($live_id,$type,$channel_user_id){
        return max(doubleval(self::query()
            ->where('live_id','=',$live_id)
            ->where('type','=',$type)
            ->where('channel_user_id','=',$channel_user_id)
            ->value('counts')),0);
    }

}
