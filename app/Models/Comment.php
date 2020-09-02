<?php

namespace App\Models;
Use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;

class Comment extends Base
{
    protected $table = 'nlsg_comment';
    protected $fillable = ['user_id','pid','relation_id','content','type','status'];

    /**
     * 想法
     * @param  int  $type 类型 1.专栏 2.讲座 3.听书 4.精品课
     */
    public function getIndexComment($id, $type=1, $uid=0, $order=1, $self=false)
    {
        if (!$id){
            return false;
        }
        if ($type ==1||$type ==2) {
            $res = Column::where('id',$id)->first()->toArray();
        } elseif($type==3 || $type==4){
            $res = Works::where('id',$id)->first()->toArray();
        }else{
            $res = Wiki::where('id',$id)->first()->toArray();
        }

        $order = $order ==1 ? 'reply_num': 'created_at';
        $lists = Comment::with(['user:id,nickname,headimg','quote:id,pid,content', 'attach:id,relation_id,img',
                    'reply'=>function($query){
                        $query->select('id','comment_id','from_uid','to_uid','content','created_at')
                            ->where('status', 1)
                            ->limit(5);
                    },
                    'reply.from_user:id,nickname', 'reply.to_user:id,nickname'])
                ->select('id','pid', 'user_id', 'relation_id', 'content','forward_num','share_num','like_num','reply_num','created_at')
                ->where('type', $type)
                ->where('relation_id', $id)
                ->where('status', 1)
                ->when($self, function ($query) use ($res) {
                    return $query->where('user_id', $res['user_id']);
                })
                ->orderBy($order,'desc')
                ->paginate(10)
                ->toArray();

        if($lists['data']){
            foreach ($lists['data'] as &$v) {
                $isLike = Like::where(['relation_id'=>$v['id'], 'type'=>1,'user_id'=>$uid])->first();
                $v['is_like'] = $isLike ? 1 : 0;
            }
        }
        return $lists;
    }


    public  function  getCommentList($id, $uid, $page=1)
    {
        if (!$id){
            return false;
        }

        $comment = Comment::with([
                                'user:id,nickname,headimg',
                                'quote:id,pid,content',
                                'attach:id,relation_id,img',
                                'reward' => function($query){
                                    $query->select('id','user_id','relation_id')
                                          ->where('type', 16)
                                          ->where('status', 1);
                                },
                                'reward.user:id,nickname,headimg'
                            ])
                       ->select('id','pid', 'user_id', 'relation_id','is_quality','content','forward_num','share_num','like_num','reply_num','reward_num','created_at','type')
                       ->where('status', 1)
                       ->find($id);
                       
        $follow = UserFollow::where(['from_uid'=>$uid,'to_uid'=>$comment->user_id])->first();  
              
        $comment['is_follow'] = $follow ? 1 : 0;

        if(in_array($comment['type'], [1, 2])){
            $comment['column'] = Column::find($comment['relation_id'], ['title','subtitle','cover_pic']);
        }elseif(in_array($comment['type'], [3, 4])){
            $comment['works']  = Works::find($comment['relation_id'], ['title','subtitle','cover_img']);
        }else{
            $comment['wiki']   = Wiki::find($comment['relation_id'], ['name','cover']);
        }

        $reply = CommentReply::with([
                        'from_user:id,nickname,headimg',
                        'to_user:id,nickname,headimg'
                  ])
                 ->select(['id','from_uid','to_uid','content','created_at'])
                 ->where('status', 1)
                 ->paginate(10)
                 ->toArray();
        $comment['reply'] = $reply['data'];

        return $comment;

    }

    public  function  reward()
    {
        return $this->hasMany(Order::class, 'relation_id', 'id');
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
