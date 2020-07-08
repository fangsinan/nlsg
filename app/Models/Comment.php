<?php

namespace App\Models;


class Comment extends Base
{
    protected $table = 'nlsg_comment';
    protected $fillable = ['user_id','pid','relation_id','content','type','status'];

    /**
     * 想法
     * @param  int  $type 类型 1.专栏
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getIndexComment($type=1)
    {
        $lists = Comment::with(['user:id,nickname','quote:pid,content', 'attach:id,relation_id,img',
                    'reply'=>function($query){
                        $query->select('id','comment_id','from_uid','to_uid','content')
                            ->where('status', 1)
                            ->limit(3);
                    },
                    'reply.from_user:id,nickname', 'reply.to_user:id,nickname'])
                ->where('type', $type)
                ->where('status', 1)
                ->paginate(10);
        return $lists;
    }
    public  function  quote()
    {
        return $this->hasOne(Comment::class, 'pid', 'id');
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
