<?php

namespace App\Models;

use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;
use EasyWeChat\Kernel\Messages\ShortVideo;

class Comment extends Base
{
    const DB_TABLE = 'nlsg_comment';
    protected $table = 'nlsg_comment';
    protected $fillable = ['user_id', 'pid', 'relation_id', 'content', 'type', 'status', 'info_id','app_project_type'];

    /**
     * 想法
     * @param  int  $type  类型 1.专栏 2.讲座 3.听书 4.精品课  6.训练营
     */
    public function getIndexComment($id, $type = 1, $uid = 0, $order = 1, $self = false, $info_id = 0)
    {

        if (empty($id)) {
            return false;
        }


        $like_type = 1;
        if ($type == 1 || $type == 2 || $type == 6) {
            $res = Column::where('id', $id)->first();
        } elseif ($type == 3 || $type == 4) {
            //$res = WorksInfo::where('id',$id)->first()->toArray();
            $res = Works::where('id', $id)->first();
        } elseif ($type == 5 ) {
            $res = Wiki::where('id', $id)->first();
            $like_type = 2;
        } elseif ($type == 7 ) {  //短视频
            $res = ShortVideoModel::where('id', $id)->first();
            $like_type = 3;
        }

        $orderBy = $order == 1 ? 'reply_num' : 'created_at';
        $query = Comment::with([
            'user:id,nickname,headimg,is_author', 'quote:id,pid,content', 'attach:id,relation_id,img',
            'reply' => function ($query) {
                $query->select('id', 'comment_id', 'from_uid', 'to_uid', 'content', 'created_at','reply_pid')
                    ->where('status', 1);
                //->limit(5); limit是只显示列表评论总体的5条回复
            },
            'reply.from_user:id,nickname,headimg,is_author', 'reply.to_user:id,nickname,headimg,is_author'
        ])
            ->select('id', 'pid', 'user_id', 'relation_id', 'info_id', 'content', 'forward_num',
                'share_num', 'like_num', 'reply_num', 'created_at', 'is_quality','is_top')
            ->where('relation_id', $id)
            ->where('type', $type)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->where('status', 1);

        if ($info_id) {
            $query->where('info_id', $info_id);
        }

        $query->whereHas('user', function ($q) {
            $q->where('id', '>', 0);
        });

        $query->when($self, function ($query) use ($res) {
                return $query->where('user_id', $res['user_id']);
            });

        if ($type == 6 && $order==2) { //训练营最新评论排序
            $query->orderBy('created_at', 'desc');
        }else {
//            ->orderBy($orderBy, 'desc')
            $query->orderBy('is_top', 'desc')
                ->orderBy('reply_num', 'desc')
                ->orderBy('created_at', 'desc');
        }
        $lists=$query->paginate(10)
            ->toArray();

        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $v['user']['new_vip'] = VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

                //需求变化需要展示回复【回复者】的评论内容
                if(!empty($v['reply'])){
                    foreach ($v['reply'] as $rep_k=>$rep_v){
                        // 是否会员
                        $v['reply'][$rep_k]['from_user']['new_vip'] = VipUser::newVipInfo($rep_v['from_user']['id']??0)['vip_id'] ?1:0;
                        $v['reply'][$rep_k]['to_user']['new_vip'] = VipUser::newVipInfo($rep_v['to_user']['id']??0)['vip_id'] ?1:0;
                        // 是否关注
                        $v['reply'][$rep_k]['from_user']['is_follow'] = UserFollow::IsFollow($uid, $rep_v['from_user']['id']??0);
                        $v['reply'][$rep_k]['to_user']['is_follow'] = UserFollow::IsFollow($uid, $rep_v['to_user']['id']??0);

                        $v['reply'][$rep_k]['is_like'] = Like::isLike($rep_v['id'],2,$uid,$like_type);
                        $v['reply'][$rep_k]['created_at'] = History::DateTime($rep_v['created_at']);
                        $v['reply'][$rep_k]['reply'] = $this->getReplay($rep_v['id'],$uid,$like_type);

                    }
                }


                $v['is_follow'] = UserFollow::IsFollow($uid, $v['user_id']);
                // $v['is_follow'] = $follow ? 1 : 0;
                $v['is_like'] = Like::isLike($v['id'],1,$uid,$like_type);
                if($type != 7){
                    //只展示五条
                    $v['reply'] = array_slice($v['reply'], 0, 5);
                }
                $v['created_at'] = History::DateTime($v['created_at']);
            }
        }
        return $lists;
    }


    //递归查询回复者的多级评论
    function getReplay($pid,$uid,$like_type){
        $subs = [];

        $reply_data = CommentReply::with([
            'from_user:id,nickname,headimg,is_author', 'to_user:id,nickname,headimg,is_author'
        ])->where(['reply_pid'=>$pid,'status'=>1])->get()->toArray();

        if(!empty($reply_data)){
            foreach ($reply_data as $getReplay_key=>$getReplay_val){
                //是否会员
                $getReplay_val['from_user']['new_vip'] = VipUser::newVipInfo($getReplay_val['from_user']['id']??0)['vip_id'] ?1:0;
                $getReplay_val['to_user']['new_vip'] = VipUser::newVipInfo($getReplay_val['to_user']['id']??0)['vip_id'] ?1:0;
                //是否关注
                $getReplay_val['from_user']['is_follow'] = UserFollow::IsFollow($uid, $getReplay_val['from_user']['id']??0);
                $getReplay_val['to_user']['is_follow'] = UserFollow::IsFollow($uid, $getReplay_val['to_user']['id']??0);



                $getReplay_val['is_like']       = Like::isLike($getReplay_val['id'],2,$uid,$like_type);
                $getReplay_val['created_at']    = History::DateTime($getReplay_val['created_at']);
                $getReplay_val['reply'] = $this->getReplay($getReplay_val['id'],$uid,$like_type);
                $subs[] = $getReplay_val;
            }
        }
        return $subs;

    }



    public function getCommentList($id, $uid, $page = 1)
    {
        if ( ! $id) {
            return false;
        }
        $comment = Comment::with([
            'user:id,nickname,headimg,is_author',
            'quote:id,pid,content',
            'attach:id,relation_id,img',
            'reward' => function ($query) {
                $query->select('id', 'user_id', 'relation_id')
                    ->where(['type' => 5, 'reward_type' => 3, 'status' => 1])
                    ->groupBy('user_id');
            },
            'reward.user:id,nickname,headimg,is_author'
        ])
            ->select('id', 'pid', 'user_id', 'relation_id', 'is_quality', 'content',
                'forward_num', 'share_num', 'like_num', 'reply_num', 'reward_num', 'created_at', 'type')
            ->where(['id' => $id, 'status' => 1,'app_project_type'=>APP_PROJECT_TYPE])
            ->first();
        if ( ! $comment) {
            return false;
        }
        $comment['is_follow'] = 0;
        if ($uid) {
            $follow = UserFollow::where(['from_uid' => $uid, 'to_uid' => $comment->user_id])->first();
            $comment['is_follow'] = $follow ? 1 : 0;
        }

        if (in_array($comment['type'], [1, 2, 6])) {
            $comment['column'] = Column::find($comment['relation_id'], ['name as title', 'subtitle', 'cover_pic', 'user_id']);

            $user = User::select('nickname', 'teacher_title', 'headimg', 'headcover','intro')->find($comment['column']['user_id']);
            $comment['column']['teacher_nickname'] = $user['nickname'];
            $comment['column']['teacher_title'] = $user['teacher_title'];

        } elseif (in_array($comment['type'], [3, 4])) {
            $comment['works'] = Works::find($comment['relation_id'], ['title', 'subtitle', 'cover_img','user_id']);
        //    $workinfo = WorksInfo::select('pid')->where('id', $comment['relation_id'])->first();
        //    if ($workinfo){
        //        $works   = Works::select('title','subtitle','cover_img')->where('id', $workinfo['pid'])->first();
        //        $comment['works']  = $works;
        //    }
            $user = User::select('nickname', 'teacher_title', 'headimg', 'headcover','intro')
                ->find($comment['works']['user_id']);
            $comment['works']['teacher_nickname'] = $user['nickname'];
            $comment['works']['teacher_title'] = $user['teacher_title'];
        } else {
            $comment['wiki'] = Wiki::find($comment['relation_id'], ['name', 'cover']);
        }

        $reply = CommentReply::with([
            'from_user:id,nickname,headimg,is_author',
            'to_user:id,nickname,headimg,is_author'
        ])
            ->select(['id', 'from_uid', 'to_uid', 'content', 'created_at'])
            ->where('comment_id', $id)
            ->where('status', 1)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->paginate(10)
            ->toArray();
        if ($reply['data']) {
            foreach ($reply['data'] as &$v) {
                // $like_type = 1;
                // if ($comment['type'] == 5) { //百科
                //     $like_type = 2;
                // } elseif ($comment['type'] == 7 ) {  //短视频
                //     $like_type = 3;
                // }
                $v['is_like'] = Like::isLike($v['id'],2,$uid);
                // $isLike = Like::where(['relation_id' => $v['id'], 'type' => $like_type, 'user_id' => $uid])->first();
                // $v['is_like'] = $isLike ? 1 : 0;
                $v['from_user']['new_vip'] = VipUser::newVipInfo($v['from_user']['id']??0)['vip_id'] ?1:0;
                $v['to_user']['new_vip'] = VipUser::newVipInfo($v['to_user']['id']??0)['vip_id'] ?1:0;
                // 是否关注
                $v['from_user']['is_follow'] = UserFollow::IsFollow($uid, $v['from_user']['id']??0);
                $v['to_user']['is_follow'] = UserFollow::IsFollow($uid, $v['to_user']['id']??0);

            }
        }
        $comment['reply'] = $reply['data'];
        $comment['reward_num'] = $comment['reward'] ? count($comment['reward']) : 0;

        // $like_type = 1;
        // if ($comment['type'] == 5 ) {
        //     $like_type = 2;
        // } elseif ($comment['type'] == 7 ) {  //短视频
        //     $like_type = 3;
        // }

        $comment['is_like'] = Like::isLike($comment['id'],1,$uid);
        $comment['user']['new_vip'] = VipUser::newVipInfo($comment['user_id'])['vip_id'] ?1:0;
        return $comment;

    }

    public function reward()
    {
        return $this->hasMany(Order::class, 'relation_id', 'id')
            ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function quote()
    {
        return $this->hasOne(Comment::class, 'id', 'pid')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function reply()
    {
        return $this->hasMany(CommentReply::class, 'comment_id', 'id')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attach()
    {
        return $this->hasMany(Attach::class, 'relation_id', 'id')
                    ->where('type', 1)
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public static function convert($lists)
    {
        if ( ! $lists) {
            return false;
        }
        if ($lists) {
            foreach ($lists['data'] as $k => &$v) {
                if ($v['type'] == 1) {
                    $lists['data'][$k]['title'] = Column::where(['id'=> $v['relation_id'], 'type' => 1
                    ])->value('name');
                } elseif ($v['type'] == 2) {
                    $lists['data'][$k]['title'] = Column::where(['id'=> $v['relation_id'], 'type' => 2
                    ])->value('name');
                } elseif ($v['type'] == 3) {
                    $lists['data'][$k]['title'] = Works::where(['id' => $v['relation_id'], 'is_audio_book' => 1
                    ])->value('title');
                } elseif ($v['type'] == 4) {
                    $lists['data'][$k]['title'] = Works::where(['id'=>$v['relation_id']])->value('title');
                    $lists['data'][$k]['chapter'] = WorksInfo::where(['id'=>$v['info_id']])->value('title');
                } elseif ($v['type'] == 5) {
                    $lists['data'][$k]['title'] = Wiki::where(['id'=> $v['relation_id']])->value('name');
                } elseif ($v['type'] == 6) {
                    $lists['data'][$k]['title'] = Column::where(['id'=> $v['relation_id'], 'type' => 3
                    ])->value('name');
                }
            }
        }
        return $lists['data'];

    }

    public function column()
    {
        return $this->belongsTo(Column::class, 'relation_id', 'id')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function work()
    {
        return $this->belongsTo(Works::class, 'relation_id', 'id')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function wiki()
    {
        return $this->belongsTo(Wiki::class, 'relation_id', 'id')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }

    public function info()
    {
        return $this->belongsTo(WorksInfo::class, 'info_id', 'id')
                    ->where('app_project_type','=',APP_PROJECT_TYPE);
    }


}
