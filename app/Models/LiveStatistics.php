<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LiveStatistics extends Model
{
    protected $table = 'nlsg_live_statistics';

    protected $fillable = [
        'live_id', 'type', 'channel_user_id', 'counts', 'created_at', 'updated_at'
    ];

    public static function getCounts($live_id, $type, $channel_user_id) {
        return max(doubleval(self::query()
            ->where('live_id', '=', $live_id)
            ->where('type', '=', $type)
            ->where('channel_user_id', '=', $channel_user_id)
            ->value('counts')), 0);
    }


    public static function countsJob($live_id, $type, $channel_user_id, $num = 1): bool {

        if (!$live_id || !$type || !$channel_user_id) {
            return false;
        }

        $res = LiveStatistics::query()
            ->updateOrCreate([
                'live_id'         => $live_id,
                'type'            => $type,
                'channel_user_id' => $channel_user_id
            ], [
                'counts' => DB::raw('counts + ' . $num)
            ]);

        if (!$res) {
            return true;
        }

        return false;

    }


}
