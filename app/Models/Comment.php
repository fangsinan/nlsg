<?php

namespace App\Models;


class Comment extends Base
{
    protected $table = 'nlsg_comment';
    protected $fillable = ['user_id','pid','relation_id','content','type','status'];

    /**
     * 想法
     * @param  int  $type 类型 1.专栏 2.讲座 3.听书 4.精品课
     */
    public function getIndexComment($id, $type=1)
    {
        if (!$id){
            return false;
        }
        $lists = Comment::with(['user:id,nickname,headimg','quote:id,pid,content', 'attach:id,relation_id,img',
                    'reply'=>function($query){
                        $query->select('id','comment_id','from_uid','to_uid','content')
                            ->where('status', 1)
                            ->limit(5);
                    },
                    'reply.from_user:id,nickname', 'reply.to_user:id,nickname'])
                ->select('id','pid', 'user_id', 'relation_id', 'content','forward_num','share_num','like_num','reply_num')
                ->where('type', $type)
                ->where('relation_id', $id)
                ->where('status', 1)
                ->paginate(10)
                ->toArray();
        return $lists;
    }
    public  function  quote()
    {
        return $this->hasOne(Comment::class, 'id', 'pid');
    }

    public function reply()
    {
        return $this->hasMany(CommentReply::class, 'comment_id', 'id');
    }

    public function user()
    {
        return  $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attach()
    {
        return $this->hasMany(Attach::class, 'relation_id', 'id')->where('type', 1);
    }
}
