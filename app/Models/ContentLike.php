<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentLike extends Model
{
    protected $table = 'nlsg_content_like';

    // 允许批量赋值
    protected  $fillable = ['type','relation_id','user_id','info_id','status'];

    /*
     * cid   评论id
     * $ctype   1主评 2次级评论
     * $uid    用户id
     * $like_type    1想法  2百科  3短视频
     * */
    public static function isLike($type=[],$rid=1,$uid=0,$info_id){
        $is_like = 0;

        $res = self::where([
            'relation_id' => $rid,
            'info_id' => $info_id,
            'user_id' => $uid,
            'app_project_type'=>APP_PROJECT_TYPE,
            ])->whereIn('type',$type)->first();
        if($res){
            $is_like = 1;
        }
        return $is_like;
    }



    // 点赞操作
    // {@param} type 1专栏  2课程  3商品  4讲座  5训练营
    // {@param} target_id 对应id

    static function editLike($user_id = 0, $target_id = 0, $type = 0, $info_id = 0)
    {
        //处理专栏的关注信息
        if (!in_array($type, [1, 2, 3, 4, 5,])) {
            return 0;
        }
        $where = ['type' => $type, 'user_id' => $user_id, 'relation_id' => $target_id,
                  'info_id'=>$info_id,'app_project_type'=>APP_PROJECT_TYPE];
        $data = self::where($where)->first();

        if (!empty($data)) {
            //直接物理删除
            WorksInfo::where(['id' => $info_id])->decrement('like_num');
            return self::destroy($data['id']);
        } else {
            //创建
            WorksInfo::where(['id' => $info_id])->increment('like_num');
            return self::create($where);
        }
    }
}
