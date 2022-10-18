<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Talk extends Base
{
    protected $table = 'nlsg_talk';

    protected $fillable = [
        'user_id', 'status','is_finish',
    ];

    /**
     * getTalkId 获取有效talk id   存在未解决问题返回  不存在则新建会话id
     *
     * @param $uid
     *
     * @return mixed
     */
    public static function getTalkId($uid)
    {
        $id = self::where([
            "user_id" => $uid,
            "is_finish" => 1,
        ])->value("id");
        if(empty($id )){
            $id = self::insertGetId([
                "user_id"    => $uid,
            ]);
        }

        return $id;
    }

    public function userInfo(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function adminInfo(): HasOne
    {
        return $this->hasOne(BackendUser::class,'id','finish_admin_id');
    }

}
