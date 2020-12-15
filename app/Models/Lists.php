<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lists extends Model
{
    protected $table = 'nlsg_lists';
    protected $fillable = [
        'title','subtitle','status'
    ];
    /**
     * 首页听书推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexListWorks($ids, $type=1)
    {
        if (!$ids){
            return false;
        }

       $lists  = Lists::with(['listWorks:id,lists_id,type,works_id'])
            ->select('id','title', 'subtitle','cover','num')
            ->whereIn('id',$ids)
            ->where('type', $type)
            ->limit(3)
            ->get()
            ->toArray();

        if ($lists){
            foreach ($lists as $k=>&$v) {
                foreach ($v['list_works'] as  $kk=>&$vv) {
                    if( $vv['type'] == 2) {
                        $listen = Works::select(['id', 'user_id', 'type', 'title', 'subtitle', 'cover_img', 'original_price', 'price', 'message', 'is_free'])
                            ->with(['user' => function ($query) {
                                $query->select('id', 'nickname', 'headimg');
                            }])
                            ->where('id', $vv['works_id'])
                            ->where('is_audio_book', 1)
                            ->where('status', 4)
                            ->first();
                        $v['list_works'][$kk]['works'] = $listen;
                    } elseif ($vv['type'] == 4) {
                        $column = Column::select(['id', 'user_id', 'title', 'subtitle', 'cover_pic', 'original_price', 'price', 'message', 'is_free'])
                            ->with(['user' => function ($query) {
                                $query->select('id', 'nickname', 'headimg');
                            }])
                            ->where('id', $vv['works_id'])
                            ->where('type', 2)
                            ->where('status', 1)
                            ->first();
                        $v['list_works'][$kk]['works'] = $column;
                    }else{
                        unset( $lists[$k]['list_works'][$kk] );
                    }
                }
                $lists[$k]['list_works'] = array_values($lists[$k]['list_works']);
                $lists[$k]['num'] = count($lists[$k]['list_works']);

            }
        }
        return $lists;
    }

    public function getIndexListCourse($ids, $type=1)
    {
        if (!$ids){
            return false;
        }
        $lists  = Lists::select('id','title', 'subtitle','cover','num')
        ->with(['works'=> function($query){
            $query->select('works_id','user_id','title', 'cover_img')
                ->where('status',4)
                ->limit(3)
                ->inRandomOrder();
        }, 'works.user'=>function($query){
            $query->select('id','nickname','headimg');
        }])->whereIn('id',$ids)
            ->where('type', $type)
            ->limit(3)
            ->first();
        if($lists) $lists = $lists->toArray();
        return $lists;
    }


    public function getIndexGoods($ids) {

        $lists = Lists::query()
            ->select('id', 'title', 'subtitle', 'cover')
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->toArray();
        return $lists;
    }

    public function getRankWorks()
    {
        $lists = Lists::select('id', 'title','num','cover')
            ->with([
                'works' => function ($query) {
                    $query->select('works_id', 'user_id', 'title','subtitle', 'cover_img','chapter_num', 'subscribe_num','is_free','price');
                },
                'works.user' =>function($query){
                    $query->select('id','nickname');
                }
            ])
            ->where('type', 4)
            ->limit(3)
            ->get()
            ->toArray();
        return $lists;
    }


    public function getRankWiki()
    {
        $lists = Lists::select('id', 'title','num','cover')
            ->with([
                'listWorks'  =>function($query){
                    $query->select('id','lists_id', 'works_id');
                },
                'listWorks.wiki' => function($query){
                    $query->select('id','name','content','view_num','like_num','comment_num','cover');
                }
            ])
            ->where('type', 5)
            ->limit(3)
            ->get();

        $lists->map(function($item){
            $item->content = Str::limit($item->content, 30);
        });
        return $lists;
    }

    public  function  getRankGoods()
    {
        $lists = Lists::select('id', 'title','num','cover')
                ->with([
                    'listGoods' => function ($query) {
                        $query->select('works_id', 'name','price');
                    }
                ])
                ->where('type', 6)
                ->limit(3)
                ->get()
                ->toArray();
        return $lists;
    }

    public function listWorks()
    {
        return $this->hasMany('App\Models\ListsWork','lists_id', 'id');
    }

    public function works()
    {
        return $this->belongsToMany('App\Models\Works',
            'nlsg_lists_work', 'lists_id', 'works_id');
    }

    public function wiki()
    {
       return $this->belongsTo('App\Models\Wiki',
           'nlsg_lists_work', 'lists_id', 'works_id');
    }

    public function listGoods()
    {
        return $this->belongsToMany('App\Models\MallGoods',
            'nlsg_lists_work', 'lists_id', 'works_id');
    }




}
