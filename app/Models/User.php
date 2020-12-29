<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Medz\Laravel\Notifications\JPush\Sender as JPushSender;
use Illuminate\Support\Facades\DB;

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
        'phone', 'nickname', 'openid', 'sex', 'province', 'city', 'headimg','appleid','is_wx','unionid','inviter','login_flag'
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


    static function getLevel($uid = 0, $level = 0, $expire_time = 0)
    {
        if ( ! $uid && ! $level) {
            return 0;
        }

        if ($uid) {
            $user = User::find($uid);

        } else {
            $user['level'] = $level;
            $user['expire_time'] = $expire_time;
        }
        if (empty($user)) {
            return 0;
        }
        $user['expire_time'] = strtotime($user['expire_time']);

        //判断会员
        $time = strtotime(date('Y-m-d', time())) + 86400;
        if ( ! empty($user) && in_array($user['level'], [3, 4, 5]) && $user['expire_time'] > $time) { //会员
            return $user->level ??$user['level'];
        } else {
            return 0;
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     * 获取将存储在JWT的中的标识符token。
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

    public function toSms()
    {

    }


    static function getIncomeFlag($twitter_id, $user_id)
    {
        if ($twitter_id == $user_id) {
            return false;
        }
        $level_twitterId = self::getLevel($twitter_id);
        if ($level_twitterId > 0) {//推客级别
            $level_userId = self::getLevel($user_id);
            if ($level_twitterId <= $level_userId) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Get Notification for JPush sender.
     * @return \Medz\Laravel\Notifications\JPush\Sender
     */
    protected function routeNotificationForJpush()
    {
        return new \Medz\Laravel\Notifications\JPush\Sender([
            'platform' => 'all',
            'audience' => [
                'alias' => sprintf('user_%d', $this->id),
            ],
        ]);
    }

    public function follow()
    {
        return $this->belongsToMany('App\Models\User', 'nlsg_user_follow', 'from_uid', 'to_uid');
    }

    public function fans()
    {
        return $this->belongsToMany('App\Models\User', 'nlsg_user_follow', 'to_uid', 'from_uid');
    }

    public function works()
    {
        return $this->hasMany(Works::class, 'user_id', 'id');
    }
    public function listens()
    {
        return $this->hasMany(Works::class, 'user_id', 'id');
    }

    public function columns()
    {
        return $this->hasMany(Column::class, 'user_id', 'id');
    }

    public function lecture()
    {
        return $this->hasMany(Column::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'user_id', 'id');
    }

    public function getInvitationRecord($uid){

        return DB::table('nlsg_user_invite as i')
            ->join('nlsg_user as u','i.to_uid','=','u.id')
            ->where('i.from_uid','=',$uid)
            ->select(['u.id','u.nickname','u.headimg','i.created_at'])
            ->get();

//        return User::where('inviter','=',$uid)
//            ->orderBy('id','desc')
//            ->select(['id','nickname','headimg','created_at'])
//            ->get();

    }

    public function getHeadimgAttribute($field)
    {
        if (!empty($this->attributes['headimg'])){
            return $this->attributes['headimg'];
        }
        return $this->attributes['headimg'] = config('env.IMAGES_URL').'image/202009/13f952e04c720a550193e5655534be86.jpg';
    }

    public function channelLogin($data){
        $check = self::where('phone','=',$data['phone'])->first();
        if($check){
            return $check;
        }else{
            $model = new self();
            $model->phone = $data['phone'];
            $model->nickname = $data['nickname'];
            $model->headimg = $data['headimg'];
            $model->ref = $data['ref'];
            $res = $model->save();
            if ($res){
                return $model;
            }else{
                return ['code'=>false,'msg'=>'请重试'];
            }
        }
    }

    public static function onlySimpleInfo($user){
        return [
            'id'=>$user['id'],
            'phone'=>$user['phone'],
            'nickname'=>$user['nickname'],
            'sex'=>$user['sex'],
            'level'=>$user['level'],
            'true_level'=>$user['true_level'],
            'new_vip'=>$user['new_vip'],
        ];

    }

    public static function getTeacherInfo($user_id){

        $res['name'] = '';
        $res['title'] = '';
        $res['subtitle'] = '';


        $user = self::where('id','=',$user_id)->first('nickname');
        $col = Column::where('user_id','=',$user_id)->where('type','=',1)->select(['title','subtitle'])->first();

        if (!empty($user->nickname)){
            $res['name'] = $user->nickname;
        }

        if (!empty($col)){
            $res['title'] = $col->title;
            $res['subtitle'] = $col->subtitle;
        }

        return $res;
    }
}
