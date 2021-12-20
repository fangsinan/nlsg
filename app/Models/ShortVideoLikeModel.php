<?php


namespace App\Models;


use EasyWeChat\Kernel\Messages\ShortVideo;

class ShortVideoLikeModel extends Base
{
    protected $table = 'nlsg_short_video_like';

    //点赞 取消点赞
    function Like($id,$type=1,$is_like=0,$uid=0){
        if(empty($uid) || empty($id)){
            return ['code'=>0,'msg'=>"参数错误"];
        }
        $list = ShortVideoLikeModel::where(['relation_id'=> $id, 'user_id'=> $uid, 'type'=>$type])->first();
        if(!empty($list['status'])){
            if ($list['status'] == $is_like ){
                return ['code'=>1000,'msg'=>"不要重复操作"];
            }
            ShortVideoLikeModel::where(['relation_id'=> $id, 'user_id'=> $uid, 'type'=>$type])
                ->updtae(['status' => $is_like,'updated_at'=>date("Y-m-d H:i:s")]);

            ShortVideoModel::where('id', $id)->decrement('like_num');

        }else{
            ShortVideoLikeModel::create([
                'relation_id' => $id,
                'user_id'     => $uid,
                'type'        => $type,
                'status'      => $is_like,
            ]);
            ShortVideoModel::where('id', $id)->increment('like_num');
        }

        return ['code'=>200,'msg'=>"操作成功"];

    }

}
