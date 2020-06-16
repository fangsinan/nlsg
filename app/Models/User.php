<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PhpParser\Node\Expr\Cast\Object_;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'nlsg_user';

    static function getLevel($uid=0,$level=0,$expire_time=0){
        if(!$uid && !$level)return 0;

        if($uid){
            $user = User::find($uid);
        }else{
            $user['level'] = $level;
            $user['expire_time'] = $expire_time;
        }

        //判断会员
        $time    = strtotime(date('Y-m-d', time())) + 86400;
        if (!empty($user) && in_array ($user['level'], [3,4,5]) && $user['expire_time']>$time) { //会员
            return $user->level;
        }else{
            return 0;
        }
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


}
