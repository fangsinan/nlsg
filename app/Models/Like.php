<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'nlsg_like';

    // 允许批量赋值
    protected  $fillable = ['comment_type','relation_id','user_id','type','status'];

    public function  user(){
        return  $this->belongsTo(User::class, 'user_id', 'id');
    }

    /*
     * cid   评论id
     * $ctype   1主评 2次级评论
     * $uid    用户id
     * $like_type    1想法  2百科  3短视频
     * */
    public static function isLike($cid=0,$ctype=1,$uid=0,$like_type=3){
        $is_like = 0;

        $res = Like::where(['comment_type'=>$ctype,'relation_id' => $cid,  'user_id' => $uid, 'status'=>1])->first();
        if($res){
            $is_like = 1;
        }
        return $is_like;
    }

    /*
    * cid   评论id
    * $ctype   1主评 2次级评论
    * $uid    用户id
    * $like_type    1想法  2百科  3短视频
    * */
    public static function like_count($cid=0,$ctype=1){
        return  Like::where(['comment_type'=>$ctype,'relation_id' => $cid,   'status'=>1])->count();
    }
}
