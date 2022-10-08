<?php


namespace App\Models;

class HelpMessage extends Base
{
    protected $table = 'nlsg_help_message';

    protected $fillable = [
        'user_id', 'message','status','send_user_id'
    ];

    /**
     * GetListByUserId  获取答案
     *
     * @param  $uid
     *
     * @return array list: 答案列表  is_show_qr: 是否需显示二维码
     */
    public static function GetListByUserId($uid): array
    {
        $list = self::select("message","user_id")
            ->where("user_id",$uid)
            ->where("status",1)->get();
        if(empty($list)) return [];

        return $list->toArray();
    }

}
