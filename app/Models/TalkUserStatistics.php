<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class TalkUserStatistics extends Base
{
    protected $table = 'nlsg_talk_user_statistics';

    protected $fillable = [
        'user_id', 'msg_count','is_finish'
    ];

    static public function msgCount(int $user_id, $flag = '+')
    {
        self::query()->updateOrCreate(
            [
                'user_id' => $user_id
            ],
            [
                'msg_count' => DB::raw('msg_count ' . $flag . ' 1'),
                'is_finish' => 1
            ]
        );
    }

    public function userInfo(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
