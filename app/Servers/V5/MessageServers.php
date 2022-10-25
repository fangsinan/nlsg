<?php

namespace App\Servers\V5;

use App\Models\Column;
use App\Models\Comment;
use App\Models\Message\MessageType;
use App\Models\Message\MessageUser;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageServers
{

    static function clear_msg($user_id,$type=[]){

        $query=MessageUser::query()->where('status',1)
            ->where('is_send', 3)
            ->where('receive_user', $user_id);
        if($type){
            $query->whereIn('type', $type);
        }

        $query->update(['status'=>2]);
    }

    static function get_user_unread_count($type,$user_id){
        return MessageUser::query()
            ->whereIn('type', $type)
            ->where('status', 1)
            ->where('is_del', 1)
            ->where('is_send', 3)
            ->where('receive_user', $user_id)->count();
    }

    static function get_user_new_msg($type_arr,$user_id){

        $MessageUser= MessageUser::query()
            ->select(['id', 'type', 'message_id','created_at','plan_time'])
            ->with([
                'message:id,type,title,message',
            ])
            ->whereIn('type', $type_arr)
            ->where('is_del', 1)
            ->where('is_send', 3)
            ->where('receive_user', $user_id)->orderBy('id','desc')->first();


        return $MessageUser;
    }

    static function get_info_by_comment($comment_id,$items){

        //获取评论
        $Comment = Comment::query()
            ->with(['user:id,nickname,headimg,is_author'])
            ->select('id',  'user_id', 'type','relation_id', 'info_id', 'content')
            ->where('id', $comment_id)
            ->first();
        if (!$Comment) {
            return $items;
        }

        $items['comment'] = $Comment;

        //1.专栏 2.讲座 3.听书 4.精品课 5 百科 6训练营  7短视频
        if(in_array($Comment->type,[1,2,6])){

            //获取训练营、专栏、讲座 details_pic横图  cover_pic竖图
            $Column = Column::query()->where('id', $Comment->relation_id)
                ->select(['id','title','subtitle', 'details_pic','cover_pic'])->first();
            $items['content'] = $Column;

        }elseif(in_array($Comment->type,[3,4])){
            //获取听书、精品课 detail_img 横图 cover_img 竖图
            $works = Works::query()->where('id', $Comment->relation_id)
                ->select(['id', 'title','subtitle', 'detail_img as details_pic','cover_img as cover_pic'])->first();
            $items['content'] = $works;
        }

        //获取章节
        if ($Comment->info_id) {

            $WorksInfo=WorksInfo::query()->where(['id' => $Comment->info_id])->select('id', 'title')->first();
            if($WorksInfo){
                $items['works_info_id'] = $WorksInfo['id'];
                $items['content']['subtitle']=$WorksInfo['title'];
            }

        }

        return  $items;

    }

//    static function get_info_by_comment2($comment_id,$items){
//
//        //获取评论
//        $Comment = Comment::query()
//            ->with(['user:id,nickname,headimg,is_author'])
//            ->select('id',  'user_id', 'type','relation_id', 'info_id', 'content')
//            ->where('id', $comment_id)
//            ->first();
//        if (!$Comment) {
//            return $items;
//        }
//
//        $items['comment'] = $Comment;
//
//        //1.专栏 2.讲座 3.听书 4.精品课 5 百科 6训练营  7短视频
//        if(in_array($Comment->type,[1,2,6])){
//
//            //获取训练营、专栏、讲座
//            $Column = Column::query()->where('id', $Comment->relation_id)
//                ->select(['id','title', 'cover_pic', 'details_pic'])->first();
//            $items['content'] = $Column;
//
//        }elseif(in_array($Comment->type,[3,4])){
//            //获取听书、精品课
//            $works = Works::query()->where('id', $Comment->relation_id)
//                ->select(['id', 'title', 'cover_img as cover_pic', 'detail_img as details_pic '])->first();
//            $items['content'] = $works;
//        }
//
//        //获取章节
//        if ($Comment->info_id) {
//            $items['works_info'] = WorksInfo::query()->where(['id' => $Comment->info_id])->select('id', 'title')->first();
//        }
//
//        return  $items;
//
//    }
}
