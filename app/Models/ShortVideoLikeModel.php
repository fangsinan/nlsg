<?php


namespace App\Models;


class ShortVideoLikeModel extends Base
{
    protected $table = 'nlsg_short_video_like';

    protected $fillable = [
        'relation_id', 'user_id', 'type', 'status',
    ];


    //点赞 取消点赞
    function Like($id,$type=1,$is_like=0,$uid=0){
        if(empty($uid) || empty($id)){
            return ['code'=>0,'msg'=>"参数错误"];
        }
        $list = ShortVideoLikeModel::where(['relation_id'=> $id, 'user_id'=> $uid, 'type'=>$type])->first();

        if(!empty($list)){
            if ($list['status'] == $is_like ){
                return ['code'=>1000,'msg'=>"重复操作"];
            }
            ShortVideoLikeModel::where(['relation_id'=> $id, 'user_id'=> $uid, 'type'=>$type])
                ->update(['status' => $is_like]);

        }else{

            ShortVideoLikeModel::firstOrCreate([
                'relation_id' => $id,
                'user_id'     => $uid,
                'type'        => $type,
                'status'      => $is_like,
            ]);
        }

        if($is_like == 0){
            ShortVideoModel::where('id', $id)->decrement('like_num');
        }else{
            ShortVideoModel::where('id', $id)->increment('like_num');
        }

        return ['code'=>200,'msg'=>"操作成功"];

    }

}
