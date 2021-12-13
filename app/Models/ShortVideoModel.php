<?php


namespace App\Models;


class ShortVideoModel extends Base
{
    protected $table = 'nlsg_short_video';


    //获取短视频
    function getVideo ($uid,$page){
        //按照rand、创建时间排序
        $field = ["id","user_id","share_img","title","introduce","view_num","like_num","comment_num","share_num","duration","url"];
        $data = self::select($field)->where('status',2)
            ->orderBy('rank','desc')->orderBy("created_at","desc")//->first();
            ->offset(($page - 1))->first()->toArray();

        $data['user_info'] = User::getTeacherInfo($data['user_id']);

        $follow = UserFollow::where(['from_uid'=>$uid,'to_uid'=>$data['user_id']])->first();
        $data['user_info']['is_follow'] = $follow ? 1 :0;
        //是否点赞
        $isLike = Like::where(['relation_id' => $data['id'], 'type' => 3, 'user_id' => $uid])->first();
        $data['user_info']['is_like'] = $isLike ? 1 : 0;

        //推荐
        $recomObj = new ShortVideoRecommedModel();
        $data["recomment"] = $recomObj->getRecomment($data['id']);

        return $data;
    }
}
