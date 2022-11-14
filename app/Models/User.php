<?php

namespace App\Models;

use App\Servers\V5\WechatServers;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Libraries\ImClient;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Medz\Laravel\Notifications\JPush\Sender as JPushSender;
use Illuminate\Support\Facades\DB;
use DateTimeInterface;

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
        'phone', 'nickname', 'openid', 'sex', 'province', 'city', 'headimg','appleid','is_wx','unionid','inviter','login_flag','isNoLogin','wxopenid','push','is_staff','is_browser'
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

    public function imUser()
    {
        return $this->hasOne(ImUser::class, 'tag_im_to_account', 'id');
    }

    public function vipUser(){
        return $this->hasOne(VipUser::class,'user_id','id')
            ->where('status','=',1)
            ->where('is_default','=',1);
    }

    public function getLName()
    {
        return $this->hasOne(BackendLiveRole::class, 'son_id','id');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function getInvitationRecord($uid){

        return DB::table('nlsg_user_invite as i')
            ->join('nlsg_user as u','i.to_uid','=','u.id')
            ->where('i.from_uid','=',$uid)
            ->orderBy('i.id','desc')
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
			if ($check->nickname != $data['nickname']){
			    $check->nickname = $data['nickname'];
			    $check->save();
			}
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

        $res['id'] = $user_id;
        $res['name'] = '';
        $res['title'] = '';
        $res['subtitle'] = '';
        $res['headimg'] = '';
        $res['vip_headcover'] = '';


        $user = self::select("nickname","headimg","vip_headcover","teacher_title")->where('id','=',$user_id)->first();
        $col = Column::where('user_id','=',$user_id)->where('type','=',1)->select(['title','subtitle'])->first();

        if (!empty($user->nickname)){
            $res['name'] = $user->nickname;
            $res['headimg'] = $user->headimg;
            $res['vip_headcover'] = $user->vip_headcover;
            $res['title'] = $user->teacher_title;
        }

        if (!empty($col)){
            $res['title'] = $col->title;
            $res['subtitle'] = $col->subtitle;
        }

        return $res;
    }

    //校验助手是否合法
    public function checkHelper($params)
    {
        if ($params['helper'] ?? false) {
            $helper = preg_replace('/[^0-9]/i', ',', $params['helper']);
            $helper = explode(',', $helper);

            $check_user = User::whereIn('phone', $helper)->select(['id', 'phone'])->get();
            if ($check_user->isEmpty()) {
                return error(1000, '未查询到该手机号信息');
            } else {
                $check_user = $check_user->toArray();
                $check_user = array_column($check_user, 'phone');

                $diff = array_diff($helper, $check_user);
                if ($diff) {
                    return error(1000, '不是注册账号');
                } else {
                    return success();
                }
            }
        } else {
            return error(1000, '没有数据');
        }

    }
    public static  function expire()
    {
           $start = date('Y-m-d H:i:s', time());
           $lists = User::whereBetween('expire_time', [
                                                   Carbon::parse($start)->toDateTimeString(),
                                                   Carbon::parse('+7 days')->toDateTimeString(),
                                               ])
                              ->pluck('id')
                              ->toArray();
           if ($lists){
               $uids  = array_chunk(array_unique($lists), 100, true);
               if ($uids){
                   foreach ($uids as $item) {
                       foreach ($item as  $v){
                           JPush::pushNow(strval($v), '您的会员即将到期');
                       }
                   }
               }
           }

    }

    function getIndexUser($ids){
        return self::select("id","phone","nickname","sex","city","headimg","teacher_title")->whereIn('id',$ids)->get()->Toarray();
    }



    //获取用学习时长
    public static function getUserHisLen($size=3){

        $week_day       = getWeekDay();
        $week_one       = $week_day['monday'];
        $top_week_one   = $week_day['top_monday'];


        $cache_key_name = 'user_his_len_list_'.$size.'_'.$top_week_one;
        $result = Cache::get($cache_key_name);
        if ($result) {
            return $result;
        }

        //时间小于本周一    大于上周一
        $his_data = History::select("user_id")->selectRaw('sum(time_number) as num')
            ->where('created_at','>',$top_week_one)
            ->where('created_at','<',$week_one)
            ->where('time_number','>',0)
            // ->where('is_del',0)
            ->orderBy('num', 'desc')->GroupBy("user_id")->limit($size)->get()->toArray();
        //重新统计num
        if($size != 3){
            Lists::where(['type'=>9])->update(['num'=>count($his_data)]);
        }
        $user = [];
        if(!empty($his_data)){
            $user_ids = array_column($his_data,'user_id');

            $user = User::select('id','nickname', 'phone','headimg')
                ->whereIn('id', $user_ids)
                ->orderByRaw('FIELD(id,'.implode(',', $user_ids).')')
                ->get()->toArray();


            foreach ($user as &$user_v){
                foreach ($his_data as $his_datum){
                    if($user_v['id'] == $his_datum['user_id']){
                        $user_v['his_num_n'] = $his_datum['num'];
    //                    $user_v['his_num'] = (floor($his_datum['num'] / 3600))."小时".($his_datum['num']%3600).'分钟';
                        $user_v['his_num'] = SecToTime($his_datum['num']);
                    }
                }
            }
        }

        Cache::put($cache_key_name, $user, 86400);
        return $user;
    }

    public function userWechat(){
        return $this->hasOne('App\Models\UserWechat','unionid','unionid')
//            ->whereNotNull('follow_user_userid')
            ->whereNotIn('follow_user_userid',[
                'JiaZhengZe', 'DongRuiXia', 'SunYiHao',
                'XuHongRu', 'ZhangJing', 'LiuDanHua', 'ShenShuJing',
                'ZhangShiHao', 'ZhangQi01'
            ]);
    }

    /**
     * SetUnionid  通过uid 的openid 和 transaction_id  获取unionid
     *
     * @param $uid  '用户id'
     * @param $transaction_id '订单号'
     */
    public static function SetUnionid($uid,$transaction_id){

        $user = User::where("id",$uid)->first();
        if(!empty($user['unionid'])){  //存在unionid   不处理
            return ;
        }
        if(empty($user['wxopenid'])){  //openid 不存在  不处理
            return ;
        }

        //获取unionid
        $openId = $user['wxopenid'];
        $res = ImClient::curlGet("https://api.weixin.qq.com/wxa/getpaidunionid?access_token=".
            WechatServers::GetToken()."&openid=$openId&transaction_id=".$transaction_id);
        $res= json_decode($res,true);
        if(!empty($res['errcode']) && $res['errcode'] == "40001"){  // token失效 重试
            $res = ImClient::curlGet("https://api.weixin.qq.com/wxa/getpaidunionid?access_token=".
                WechatServers::GetToken(true)."&openid=$openId&transaction_id=".$transaction_id);
            $res= json_decode($res,true);
        }

        //更新unionid
        if(!empty($res['unionid'])){
            // 修改unioid
            User::where("id",$uid)->update(['unionid'=>$res['unionid']]);
        }
        return;
    }
}
