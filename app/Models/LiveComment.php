<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/17
 * Time: 1:59 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveComment extends Base
{
    protected $table = 'nlsg_live_comment';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function live()
    {
        return $this->belongsTo(Live::class, 'live_id', 'id');
    }

}
