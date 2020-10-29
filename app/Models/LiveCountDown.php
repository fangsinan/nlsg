<?php
/**
 * Created by PhpStorm.
 * User: linksus
 * Date: 19/6/18
 * Time: 下午10:02
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LiveCountDown extends Model
{
    protected $table = 'nlsg_live_count_down';

    protected  $fillable = ['live_id','user_id','phone','is_send','new_vip_uid'];
}
