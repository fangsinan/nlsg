<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'nlsg_user';

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
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * 发送短信验证码
     * @param $phone
     * @return array|bool
     */

    public function  toSms()
    {

    }


    static function GetIncomeFlag($twitter_id,$user_id)
    {
        if($twitter_id==$user_id){
            return false;
        }
        $level_twitterId = self::getLevel($twitter_id);
        if($level_twitterId>0){//推客级别
            $level_userId = self::getLevel($user_id);
            if($level_twitterId<=$level_userId){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }


}
