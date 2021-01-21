<?php

namespace App\Models;

use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;

class Comment extends Base
{
    protected $table = 'nlsg_comment';
    protected $fillable = ['user_id', 'pid', 'relation_id', 'content', 'type', 'status', 'info_id'];

    /**
     * 想法
     * @param  int  $type  类型 1.专栏 2.讲座 3.听书 4.精品课
     */
    public function getIndexComment($id, $type = 1, $uid = 0, $order = 1, $self = false, $info_id = 0)
    {
        if ( ! $id) {
            return false;
        }
        if ($type == 1 || $type == 2) {
            $res = Column::where('id', $id)->first();
        } elseif ($type == 3 || $type == 4) {
            //$res = WorksInfo::where('id',$id)->first()->toArray();
            $res = Works::where('id', $id)->first();
        } else {
            $res = Wiki::where('id', $id)->first();
        }

        $order = $order == 1 ? 'reply_num' : 'created_at';
        $query = Comment::with([
            'user:id,nickname,headimg', 'quote:id,pid,content', 'attach:id,relation_id,img',
            'reply' => function ($query) {
                $query->select('id', 'comment_id', 'from_uid', 'to_uid', 'content', 'created_at')
                    ->where('status', 1);
                //->limit(5); limit是只显示列表评论总体的5条回复
            },
            'reply.from_user:id,nickname', 'reply.to_user:id,nickname'
        ])
            ->select('id', 'pid', 'user_id', 'relation_id', 'info_id', 'content', 'forward_num',
                'share_num', 'like_num', 'reply_num', 'created_at', 'is_quality')
            ->where('relation_id', $id);
        if ($info_id) {
            $query->where('info_id', $info_id);
        }

        $lists = $query
            ->where('type', $type)
            ->where('status', 1)
            ->when($self, function ($query) use ($res) {
                return $query->where('user_id', $res['user_id']);
            })
            ->orderBy($order, 'desc')
            ->paginate(10)
            ->toArray();

        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $follow = UserFollow::where(['from_uid' => $uid, 'to_uid' => $v['user_id']])->first();
                $v['is_follow'] = $follow ? 1 : 0;
                $like_type = 1;
                if ($type == 5) { //百科
                    $like_type = 2;
                }
                $isLike = Like::where(['relation_id' => $v['id'], 'type' => $like_type, 'user_id' => $uid])->first();
                $v['is_like'] = $isLike ? 1 : 0;
                //只展示五条
                $v['reply'] = array_slice($v['reply'], 0, 5);

            }
        }
        return $lists;
    }


    public function getCommentList($id, $uid, $page = 1)
    {
        if ( ! $id) {
            return false;
        }
        $comment = Comment::with([
            'user:id,nickname,headimg',
            'quote:id,pid,content',
            'attach:id,relation_id,img',
            'reward' => function ($query) {
                $query->select('id', 'user_id', 'relation_id')
                    ->where(['type' => 5, 'reward_type' => 3, 'status' => 1])
                    ->groupBy('user_id');
            },
            'reward.user:id,nickname,headimg'
        ])
            ->select('id', 'pid', 'user_id', 'relation_id', 'is_quality', 'content',
                'forward_num', 'share_num', 'like_num', 'reply_num', 'reward_num', 'created_at', 'type')
            ->where(['id' => $id, 'status' => 1])
            ->first();
        if ( ! $comment) {
            return false;
        }

        if ($uid) {
            $follow = UserFollow::where(['from_uid' => $uid, 'to_uid' => $comment->user_id])->first();
            $comment['is_follow'] = $follow ? 1 : 0;
        }

        if (in_array($comment['type'], [1, 2])) {
            $comment['column'] = Column::find($comment['relation_id'], ['title', 'subtitle', 'cover_pic']);
        } elseif (in_array($comment['type'], [3, 4])) {
            $comment['works'] = Works::find($comment['relation_id'], ['title', 'subtitle', 'cover_img']);
//            $workinfo = WorksInfo::select('pid')->where('id', $comment['relation_id'])->first();
//            if ($workinfo){
//                $works   = Works::select('title','subtitle','cover_img')->where('id', $workinfo['pid'])->first();
//                $comment['works']  = $works;
//            }
        } else {
            $comment['wiki'] = Wiki::find($comment['relation_id'], ['name', 'cover']);
        }

        $reply = CommentReply::with([
            'from_user:id,nickname,headimg',
            'to_user:id,nickname,headimg'
        ])
            ->select(['id', 'from_uid', 'to_uid', 'content', 'created_at'])
            ->where('comment_id', $id)
            ->where('status', 1)
            ->paginate(10)
            ->toArray();
        if ($reply['data']) {
            foreach ($reply['data'] as &$v) {
                $like_type = 1;
                if ($comment['type'] == 5) { //百科
                    $like_type = 2;
                }
                $isLike = Like::where(['relation_id' => $v['id'], 'type' => $like_type, 'user_id' => $uid])->first();
                $v['is_like'] = $isLike ? 1 : 0;
            }
        }
        $comment['reply'] = $reply['data'];
        $comment['reward_num'] = $comment['reward'] ? count($comment['reward']) : 0;

        return $comment;

    }

    public function reward()
    {
        return $this->hasMany(Order::class, 'relation_id', 'id');
    }

    public function quote()
    {
        return $this->hasOne(Comment::class, 'id', 'pid');
    }

    public function reply()
    {
        return $this->hasMany(CommentReply::class, 'comment_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attach()
    {
        return $this->hasMany(Attach::class, 'relation_id', 'id')->where('type', 1);
    }

    public static function convert($lists)
    {
        if ( ! $lists) {
            return false;
        }
        if ($lists) {
            foreach ($lists['data'] as $k=>&$v) {
                if ($v['type'] == 1) {
                    $lists['data'][$k]['title'] = Column::where(['id' => $v['relation_id'], 'type' => 1])->value('name');
                } elseif ($v['type'] == 2) {
                    $lists['data'][$k]['title']  = Column::where(['id' => $v['relation_id'], 'type' => 2])->value('name');
                } elseif ($v['type'] == 3) {
                    $lists['data'][$k]['title']  = Works::where(['id' => $v['relation_id'], 'is_audio_book' => 1])->value('title');
                } elseif ($v['type'] == 4) {
                    $lists['data'][$k]['title']  = Works::where(['id' => $v['relation_id']])->value('title');
                } elseif ($v['type'] == 5) {
                    $lists['data'][$k]['title']  = Wiki::where(['id' => $v['relation_id']])->value('name');
                }
            }
        }
        return $lists['data'];

    }

    public function column()
    {
        return $this->belongsTo(Column::class, 'relation_id', 'id');
    }

    public function work()
    {
        return $this->belongsTo(Works::class, 'relation_id', 'id');
    }
    public function wiki()
    {
        return $this->belongsTo(Wiki::class, 'relation_id', 'id');
    }


}
