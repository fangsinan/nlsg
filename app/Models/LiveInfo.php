<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/17
 * Time: 2:01 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LiveInfo extends Model
{
    protected $table = 'nlsg_live_info';


    public function live()
    {
        return $this->belongsTo(Live::class, 'live_pid', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
