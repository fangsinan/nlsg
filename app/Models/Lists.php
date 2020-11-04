<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Db;

class Lists extends Model
{
    protected $table = 'nlsg_lists';

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

        $lists  = Lists::select('id','title', 'subtitle','cover','num')
        ->with(['works'=> function($query){
            $query->select('works_id','user_id','title', 'cover_img','status')
            ->where('status',4);
        }, 'works.user'=>function($query){
            $query->select('id','nickname','headimg');
        }])->whereIn('id',$ids)
            ->where('type', $type)
            ->limit(3)
            ->get();
        if($lists) $lists = $lists->toArray();
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
            ->where('status', 1)
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




}
