<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

class TalkList extends Base
{
    protected $table = 'nlsg_talk_list';

    protected $fillable = [
        'talk_id', 'type','user_id','admin_id','content','image',
    ];



    public function user()
    {
        return  $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * GetListByUserId  获取答案
     *
     * @param  $uid
     *
     * @return array list: 答案列表  is_show_qr: 是否需显示二维码
     */
    public static function GetListByUserId($uid): array
    {
        $list = self::with(
            "user:id,nickname,headimg"
        )->select("content","user_id",'admin_id','type','image')
            ->where("user_id",$uid)
            ->where("status",1)->get();
        if(empty($list)) return [];

        $list = $list->toArray();

        foreach($list as &$val){
            //查看客服
            if($val['type'] == 2){
                // BackendUser::select("username")->find($val['admin_id']);
                $val['user']['id'] = $val['admin_id'];
                $val['user']['nickname'] = "客服";
                $val['user']['headimg'] = "image/202009/13f952e04c720a550193e5655534be86.jpg";

            }
        }

        return $list;
    }

    public function adminInfo(): HasOne
    {
        return $this->hasOne(BackendUser::class,'id','admin_id');
    }

    public function userInfo(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function talkInfo(): HasOne
    {
        return $this->hasOne(Talk::class,'id','talk_id');
    }

}
